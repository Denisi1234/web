<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FastnetApiClient;
use App\Service\BookingQuoteService;
use App\Service\PaymentService;
use Cake\Http\Response;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * BookingsController
 * Modular checkout, price calculation, and reservation management controller.
 */
class BookingsController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected BookingQuoteService $quoteService;
    protected PaymentService $paymentService;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->quoteService = new BookingQuoteService($this->apiClient);
        $this->paymentService = new PaymentService($this->apiClient);
    }

    /**
     * Booking Step 1 - Review Stay & Price Calculation
     */
    public function bookingPage()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $propertyId = !empty($queryParams['property_id']) ? (int)$queryParams['property_id'] : 0;
        $roomId = !empty($queryParams['room_id']) ? (int)$queryParams['room_id'] : null;
        // Normalize dates — support both spec aliases and provide sane defaults so quote creation never fails on missing dates
        $defaultCheckIn = date('Y-m-d', strtotime('+7 days'));
        $defaultCheckOut = date('Y-m-d', strtotime('+13 days'));
        $rawCheckIn = $queryParams['checkIn'] ?? $queryParams['check_in'] ?? $queryParams['checkin'] ?? null;
        $rawCheckOut = $queryParams['checkOut'] ?? $queryParams['check_out'] ?? $queryParams['checkout'] ?? null;
        $checkIn = $rawCheckIn ?: $defaultCheckIn;
        $checkOut = $rawCheckOut ?: $defaultCheckOut;
        // Ensure normalized dates are in queryParams for hidden inputs & quoteService
        if (empty($queryParams['checkIn']) && empty($queryParams['check_in'])) {
            $queryParams['checkIn'] = $checkIn;
        }
        if (empty($queryParams['checkOut']) && empty($queryParams['check_out'])) {
            $queryParams['checkOut'] = $checkOut;
        }
        $guests = (int)($queryParams['adults'] ?? 2) + (int)($queryParams['children'] ?? 0);
        $roomsCount = max(1, (int)($queryParams['rooms'] ?? 1));

        // 0. Resume a valid session quote (error redirects land here with only quote_id) —
        // avoids re-quoting and can never fall through to home for lack of ids.
        $resumeQuoteId = trim((string)($queryParams['quote_id'] ?? ''));
        if ($resumeQuoteId !== '') {
            $resumed = $this->getRequest()->getSession()->read('booking_quotes.' . $resumeQuoteId);
            if (is_array($resumed) && !empty($resumed['expires_at']) && (int)$resumed['expires_at'] >= time()
                && !empty($resumed['calculation']) && !empty($resumed['property']) && !empty($resumed['room'])) {
                $queryParams = array_merge($queryParams, [
                    'checkIn' => $resumed['check_in'] ?? ($queryParams['checkIn'] ?? null),
                    'checkOut' => $resumed['check_out'] ?? ($queryParams['checkOut'] ?? null),
                    'adults' => $resumed['adults'] ?? ($queryParams['adults'] ?? null),
                    'children' => $resumed['children'] ?? ($queryParams['children'] ?? null),
                    'rooms' => $resumed['rooms'] ?? ($queryParams['rooms'] ?? null),
                ]);
                $this->set(compact('queryParams') + [
                    'property' => $resumed['property'],
                    'room' => $resumed['room'],
                    'calculation' => $resumed['calculation'],
                    'quote' => $resumed,
                    'quoteError' => null,
                ]);
                return $this->render('/Pages/booking-page');
            }
        }

        // 1. Fetch Property info
        $property = null;
        if ($propertyId) {
            $propData = $this->apiClient->get('/properties/' . $propertyId);
            if (!empty($propData)) {
                $property = $propData['data'] ?? $propData;
            }
        }

        // 2. Fetch Room info - first inspect embedded rooms inside property to avoid redundant HTTP call
        $room = null;
        if (!empty($property['rooms']) && is_array($property['rooms'])) {
            if ($roomId) {
                foreach ($property['rooms'] as $r) {
                    if ((int)($r['id'] ?? 0) === (int)$roomId) {
                        $room = $r;
                        break;
                    }
                }
            }
            if (!$room && !empty($property['rooms'][0])) {
                $room = $property['rooms'][0];
                $roomId = (int)$room['id'];
            }
        }
        if (!$room && $roomId) {
            $roomData = $this->apiClient->get('/rooms/' . $roomId);
            if (!empty($roomData)) {
                $room = $roomData['data'] ?? $roomData;
            }
        }

        // 3. Authoritative price calculation via backend POST /bookings/calculate (with local fallback for offline/demo)
        $quote = null;
        $quoteError = null;
        $calculation = null;
        try {
            $quote = $this->quoteService->create($propertyId, $roomId, $queryParams);
            $this->getRequest()->getSession()->write('booking_quotes.' . $quote['quote_id'], $quote);
            $queryParams = array_merge($queryParams, [
                'quote_id' => $quote['quote_id'],
                'checkIn' => $quote['check_in'],
                'checkOut' => $quote['check_out'],
                'adults' => $quote['adults'],
                'children' => $quote['children'],
                'rooms' => $quote['rooms'],
            ]);
            $property = $quote['property'];
            $room = $quote['room'];
            $calculation = $quote['calculation'];

            /*
             * Hold the room while the guest completes checkout.
             *
             * Without this the hold endpoints were dead: nothing reserved
             * inventory between "guest selects a room" and "guest pays", so two
             * guests could both pass the availability check for the last unit
             * and the second payment would land on an already-sold room. The
             * hold expires after 10 minutes, so abandoning checkout releases it.
             *
             * Only attempted for a signed-in guest - the endpoint is behind
             * auth:sanctum. A guest booking is still protected by the
             * availability re-check inside POST /bookings/create.
             */
            $lockResult = null;
            $lockToken = $this->bearerToken();
            if ($lockToken !== '' && !empty($roomId) && !empty($quote['check_in'])) {
                $lockRes = $this->apiClient->post('/bookings/lock', [
                    'room_id'    => (int) $roomId,
                    'check_in'   => $quote['check_in'],
                    'check_out'  => $quote['check_out'],
                ], ['Authorization' => 'Bearer ' . $lockToken, 'Accept' => 'application/json']);

                if (!empty($lockRes) && empty($lockRes['_status'])) {
                    $lockResult = ['held' => true];
                    $this->getRequest()->getSession()->write('booking_lock', [
                        'room_id'   => (int) $roomId,
                        'check_in'  => $quote['check_in'],
                        'check_out' => $quote['check_out'],
                        'held_at'   => time(),
                    ]);
                } elseif ((int)($lockRes['_status'] ?? 0) === 409) {
                    // Someone else holds it - tell the guest plainly rather than
                    // letting them fill the form for a room they cannot have.
                    $this->getRequest()->getSession()->delete('booking_quotes.' . $quote['quote_id']);
                    $this->Flash->error(__($lockRes['message'] ?? 'That room was just taken. Please choose another room or date.'));
                    // Stay context for the bounce-back link.
                    $back = array_filter([
                        'city'      => $queryParams['city'] ?? ($queryParams['destination'] ?? null),
                        'checkin'   => $queryParams['checkIn'] ?? ($queryParams['check_in'] ?? ($queryParams['checkin'] ?? null)),
                        'checkout'  => $queryParams['checkOut'] ?? ($queryParams['check_out'] ?? ($queryParams['checkout'] ?? null)),
                        'property_id' => $propertyId ?: null,
                    ]);

                    return $this->redirect(
                        $propertyId
                            ? ['controller' => 'Stays', 'action' => 'detail', $propertyId, '?' => $back]
                            : ['controller' => 'Pages', 'action' => 'index', '?' => $back]
                    );
                }
            }
        } catch (\Throwable $exception) {
            // No fake fallback quote: booking without an authoritative backend price would charge
            // the wrong amount. Send the guest back to the stay with an honest reason instead.
            $detailQuery = array_filter([
                'city' => $queryParams['city'] ?? ($queryParams['destination'] ?? null),
                'checkin' => $queryParams['checkIn'] ?? ($queryParams['check_in'] ?? ($queryParams['checkin'] ?? null)),
                'checkout' => $queryParams['checkOut'] ?? ($queryParams['check_out'] ?? ($queryParams['checkout'] ?? null)),
                'adults' => $queryParams['adults'] ?? null,
                'children' => $queryParams['children'] ?? null,
                'rooms' => $queryParams['rooms'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
            if ($exception instanceof \InvalidArgumentException) {
                $this->Flash->error(__($exception->getMessage()));
            } else {
                $this->Flash->error(__('We could not reach the booking service. Please check your connection and try again.'));
            }
            if ($propertyId > 0) {
                return $this->redirect(['controller' => 'Stays', 'action' => 'detail', $propertyId, '?' => $detailQuery]);
            }
            return $this->redirect(['controller' => 'Pages', 'action' => 'index', '?' => $detailQuery]);
        }

        $this->set(compact('property', 'room', 'calculation', 'queryParams', 'quote', 'quoteError'));
        return $this->render('/Pages/booking-page');
    }

    private function parseDateOrDefault(string $value, string $default): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if ($d && $d->format('Y-m-d') === $value) return $value;
        }
        return $default;
    }

    /**
     * Booking Step 2 - Guest Details & Direct Reservation Creation
     */
    public function bookingpage02()
    {
        $request = $this->getRequest();
        $bookingResult = null;
        $errorMessage = null;

        if ($request->is('post')) {
            $postData = (array)$request->getData();
            $quoteId = trim((string)($postData['quote_id'] ?? ''));
            $quote = $quoteId !== '' ? $request->getSession()->read('booking_quotes.' . $quoteId) : null;
            if (!is_array($quote) || empty($quote['expires_at']) || (int)$quote['expires_at'] < time()) {
                $this->Flash->error(__('Your room quote has expired. Please select the room again.'));
                // Carry the quote's own ids/dates so re-pricing succeeds instead of falling through to home
                $retryQuery = [];
                if (is_array($quote)) {
                    $retryQuery = array_filter([
                        'property_id' => $quote['property_id'] ?? null,
                        'room_id' => $quote['room_id'] ?? null,
                        'checkIn' => $quote['check_in'] ?? null,
                        'checkOut' => $quote['check_out'] ?? null,
                        'adults' => $quote['adults'] ?? null,
                        'children' => $quote['children'] ?? null,
                        'rooms' => $quote['rooms'] ?? null,
                    ], fn($v) => $v !== null && $v !== '');
                }
                // Also merge any ids the POST carried (payment step re-submits context)
                foreach (['property_id', 'room_id', 'checkIn', 'check_in', 'checkOut', 'check_out', 'adults', 'children', 'rooms'] as $k) {
                    if (!isset($retryQuery[$k]) && isset($postData[$k]) && $postData[$k] !== '' && $postData[$k] !== null) {
                        $retryQuery[$k] = $postData[$k];
                    }
                }
                return $this->redirect(['action' => 'bookingPage', '?' => $retryQuery]);
            }
            if (!$this->paymentService->isConfigured()) {
                $this->Flash->error(__('Online payments are not configured yet. Please try again later.'));
                return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
            }
            
            $firstName = trim($postData['first_name'] ?? '');
            $lastName = trim($postData['last_name'] ?? '');
            $fullName = trim($firstName . ' ' . $lastName);
            if (empty($fullName)) $fullName = 'Guest Traveler';

            $paymentMethod = trim($postData['payment_method'] ?? $postData['pay_method'] ?? 'vodacom');
            // Card is not offered: there is no card gateway, so card details
            // would be collected and dropped. Reject it outright rather than
            // stranding the booking in a pending state that can never settle.
            if ($paymentMethod === 'card') {
                $this->Flash->error(__('Card payments are not available right now. Please choose a mobile money option.'));
                return $this->redirect(['action' => 'bookingpage03', '?' => ['quote_id' => $quoteId]]);
            }
            $paymentPhone = trim((string)($postData['payment_phone'] ?? ($postData['phone'] ?? '')));
            if ($paymentPhone === '') {
                $this->Flash->error(__('Enter the mobile number that should receive the payment request.'));
                return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
            }
            $guestEmail = trim((string)($postData['email'] ?? ''));
            $guestPhone = trim((string)($postData['phone'] ?? $paymentPhone));
            $paymentAccountName = trim($postData['payment_account_name'] ?? $fullName);

            $payload = [
                'property_id' => (int)$quote['property_id'],
                'room_id' => (int)$quote['room_id'],
                'check_in' => $quote['check_in'],
                'check_out' => $quote['check_out'],
                'guest_name' => $fullName,
                'guest_email' => $guestEmail,
                'guest_phone' => $guestPhone,
                'payment_method' => $paymentMethod,
                'payment_phone' => $paymentPhone,
                'payment_account_name' => $paymentAccountName,
                'guests' => (int)$quote['adults'] + (int)$quote['children'],
                'rooms' => (int)$quote['rooms'],
                'special_requests' => $postData['special_requests'] ?? null,
                'room_preference' => $postData['room_preference'] ?? null,
                'bed_preference' => $postData['bed_preference'] ?? null,
            ];

            // Create a pending booking via authoritative Laravel API POST /api/bookings/create (maps to POST /api/v1/bookings per spec)
            $payload['status'] = 'payment_pending';
            $apiResult = $this->apiClient->post('/bookings/create', $payload);

            // Handle room-locked / already booked validation (409)
            if (!empty($apiResult['_status']) && (int)$apiResult['_status'] === 409) {
                $msg = $apiResult['message'] ?? 'Room is not available for the selected dates.';
                // Clean user-facing message
                if (stripos($msg, 'not available') !== false || stripos($msg, 'already booked') !== false || stripos($msg, 'locked') !== false) {
                    $msg = 'This room was just booked for these dates. Please choose another room.';
                }
                $this->Flash->error(__($msg));
                return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
            }
            if (!empty($apiResult['message']) && empty($apiResult['id']) && empty($apiResult['booking_id']) && empty($apiResult['data'])) {
                // 422 validation or generic error without booking data
                $msg = $apiResult['message'];
                if (stripos($msg, 'not available') !== false) $msg = 'This room was just booked for these dates. Please choose another room.';
                $this->Flash->error(__($msg));
                return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
            }

            if (!empty($apiResult) && (!empty($apiResult['id']) || !empty($apiResult['booking_id']) || !empty($apiResult['data']))) {
                $bData = $apiResult['data'] ?? $apiResult;
                $bookingId = (string)($bData['id'] ?? ($bData['booking_id'] ?? ''));
                $bookingCode = (string)($bData['booking_code'] ?? $bData['reference'] ?? $bookingId);
                $totalAmount = (float)($bData['total_price'] ?? ($bData['total_amount'] ?? $quote['calculation']['total_amount']));
                if ($bookingId === '') {
                    $this->Flash->error(__('We could not start your booking. Please try again.'));
                    return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
                }
                // Store authoritative booking object (booking_code, status, invoice_url)
                $bData['_stored_at'] = time();
                if (!empty($bData['invoice_url'])) {
                    $this->getRequest()->getSession()->write('booking_invoices.' . $bookingCode, $bData['invoice_url']);
                }

// Mobile money: generate a synthetic payment ID immediately and redirect to pending page.
                // The payment-pending page fires the AzamPay USSD push via AJAX — no blocking wait here.
                $paymentId = 'TX-AZAM-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));
                $request->getSession()->write('pending_payments.' . $paymentId, [
                    'booking_id' => $bookingId,
                    'booking_code' => $bookingCode,
                    'amount' => $totalAmount,
                    'guest_email' => $guestEmail,
                    'payment_method' => $paymentMethod,
                    'payment_phone'    => $paymentPhone,
                    'account_name'     => $paymentAccountName,
                    'created_at'       => time(),
                    'push_dispatched'  => false,  // payment-pending page will dispatch via AJAX
                ]);
                $request->getSession()->delete('booking_quotes.' . $quoteId);
                return $this->redirect(['action' => 'paymentPending', '?' => ['payment_id' => $paymentId]]);
            } else {
                $this->Flash->error(__('We could not create your booking. Please try again.'));
                return $this->redirect([
                    'action' => 'bookingPage',
                    '?' => ['quote_id' => $quoteId]
                ]);
            }
        }

        $queryParams = $request->getQueryParams();
        return $this->redirect(['action' => 'bookingPage', '?' => $queryParams]);
    }

    public function paymentPending()
    {
        $paymentId = trim((string)$this->getRequest()->getQuery('payment_id', ''));
        $pending = $paymentId !== '' ? $this->getRequest()->getSession()->read('pending_payments.' . $paymentId) : null;
        if (!is_array($pending) || empty($pending['booking_id'])) {
            throw new NotFoundException(__('Payment session not found.'));
        }
        $this->set(compact('paymentId', 'pending'));
        return $this->render('/Pages/booking-payment');
    }

    /**
     * AJAX endpoint — fires AzamPay USSD push after payment-pending page has loaded.
     * Called once by the frontend JS; idempotent via push_dispatched flag in session.
     */
    public function paymentDispatch(): Response
    {
        $this->getRequest()->allowMethod(['post']);
        $body = (array)($this->getRequest()->getBody() ? json_decode((string)$this->getRequest()->getBody(), true) : []);
        $paymentId = trim((string)($body['payment_id'] ?? $this->getRequest()->getData('payment_id', '')));
        $pending = $paymentId !== '' ? $this->getRequest()->getSession()->read('pending_payments.' . $paymentId) : null;

        if (!is_array($pending) || empty($pending['booking_id'])) {
            return $this->response->withStatus(404)->withType('application/json')
                ->withStringBody(json_encode(['ok' => false, 'error' => 'session_not_found']));
        }
        // Idempotency — do not double-dispatch
        if (!empty($pending['push_dispatched'])) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['ok' => true, 'note' => 'already_dispatched']));
        }

        // Mark dispatched before calling API (prevents double-push on network retry)
        $pending['push_dispatched'] = true;
        $this->getRequest()->getSession()->write('pending_payments.' . $paymentId, $pending);

        // Fire AzamPay USSD push (non-blocking from user perspective — page is already shown)
        $checkout = $this->paymentService->initiate([
            'booking_id'     => $pending['booking_id'],
            'amount'         => $pending['amount'] ?? 0,
            'payment_method' => $pending['payment_method'] ?? 'vodacom',
            'payment_phone'  => $pending['payment_phone'] ?? '',
            'account_name'   => $pending['account_name'] ?? '',
        ]);

        // The checkout response mints the gateway transaction id (TX-AZAM-…).
        // Keep it: status polling must use THIS id, not the synthetic session
        // key. Using the session key made every poll 404, so the page could
        // never leave "pending" even after a successful webhook payment.
        if (is_array($checkout)) {
            $txn = trim((string)(
                $checkout['transaction_id']
                ?? $checkout['data']['transaction_id']
                ?? ''
            ));
            if ($txn !== '') {
                $pending['gateway_txn'] = $txn;
                $this->getRequest()->getSession()->write('pending_payments.' . $paymentId, $pending);
            }
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['ok' => true]));
    }

    public function paymentStatus(): Response
    {
        $session = $this->getRequest()->getSession();
        $paymentId = trim((string)$this->getRequest()->getQuery('payment_id', ''));
        $pending = $paymentId !== '' ? $session->read('pending_payments.' . $paymentId) : null;
        if (!is_array($pending)) {
            return $this->response->withStatus(404)->withType('application/json')->withStringBody(json_encode(['status' => 'expired']));
        }

        // Terminal states are latched on first observation. Without this a
        // gateway status string we fail to recognise later in the flow can
        // drag a settled payment back to "pending" and the page then tells the
        // guest their money was never received.
        $terminal = ['paid', 'failed', 'expired', 'review'];
        $latched = (string)($pending['status'] ?? '');
        if ($latched !== '' && in_array($latched, $terminal, true)) {
            return $this->paymentStatusResponse($latched, (int)($pending['booking_id'] ?? 0), $pending['payment_status'] ?? null);
        }

        $result = $this->paymentService->status(
            // Prefer the gateway transaction id minted by checkout; the
            // synthetic session key never resolves server-side.
            (string)($pending['gateway_txn'] ?? $paymentId),
            (string)$pending['booking_id']
        );
        $data = is_array($result) ? ($result['data'] ?? $result) : [];
        $raw = strtolower((string)($data['status'] ?? ($data['payment_status'] ?? ($data['transactionStatus'] ?? ($data['paymentStatus'] ?? 'pending')))));

        $normalized = match ($raw) {
            'paid', 'success', 'successful', 'completed', 'confirmed', 'settled', 'authorized', '00' => 'paid',
            'failed', 'cancelled', 'canceled', 'declined', 'rejected', 'error' => 'failed',
            'expired', 'timeout', 'timed_out' => 'expired',
            // Money arrived but the amount did not match. Not a retry - a human
            // has to reconcile it, so surface it instead of looping to timeout.
            'amount_mismatch' => 'review',
            default => 'pending',
        };

        if ($normalized !== 'pending') {
            $session->write('pending_payments.' . $paymentId . '.status', $normalized);
        }
        if ($normalized === 'paid') {
            $session->delete('pending_payments.' . $paymentId);
        }

        return $this->paymentStatusResponse(
            $normalized,
            (int)$pending['booking_id'],
            $raw,
            // The success page needs the booking email to verify a guest
            // booking that has no session. Without it every paid guest 404s.
            (string)($pending['guest_email'] ?? '')
        );
    }

    private function paymentStatusResponse(string $status, int $bookingId, ?string $raw, string $email = ''): Response
    {
        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => $status,
            'booking_id' => $bookingId,
            'gateway_status' => $raw,
            'email' => $email,
        ]));
    }

    public function bookingpage03()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $propertyId = !empty($queryParams['property_id']) ? (int)$queryParams['property_id'] : 0;
        $roomId = !empty($queryParams['room_id']) ? (int)$queryParams['room_id'] : null;
        // Try to load quote from session if quote_id present
        $quote = null;
        $quoteId = trim((string)($queryParams['quote_id'] ?? ''));
        if ($quoteId !== '') {
            $quote = $this->getRequest()->getSession()->read('booking_quotes.' . $quoteId);
            if (is_array($quote)) {
                $queryParams = array_merge($queryParams, [
                    'checkIn' => $quote['check_in'] ?? $queryParams['checkIn'] ?? null,
                    'checkOut' => $quote['check_out'] ?? $queryParams['checkOut'] ?? null,
                ]);
            }
        }
        $calculation = $quote['calculation'] ?? null;
        // If quote missing (e.g. session expired, direct GET, or offline fallback), try to rebuild from query params
        if (empty($quote) || empty($calculation)) {
            if ($propertyId && $roomId) {
                try {
                    // Ensure dates exist
                    $defaultCheckIn = date('Y-m-d', strtotime('+7 days'));
                    $defaultCheckOut = date('Y-m-d', strtotime('+13 days'));
                    $tmpParams = $queryParams;
                    if (empty($tmpParams['checkIn']) && empty($tmpParams['check_in'])) $tmpParams['checkIn'] = $defaultCheckIn;
                    if (empty($tmpParams['checkOut']) && empty($tmpParams['check_out'])) $tmpParams['checkOut'] = $defaultCheckOut;
                    $quote = $this->quoteService->create($propertyId, $roomId, $tmpParams);
                    $this->getRequest()->getSession()->write('booking_quotes.' . $quote['quote_id'], $quote);
                    $queryParams['quote_id'] = $quote['quote_id'];
                    $calculation = $quote['calculation'];
                } catch (\Throwable $e) {
                    // No fake fallback quote here either — restart the pricing step honestly.
                    if ($e instanceof \InvalidArgumentException) {
                        $this->Flash->error(__($e->getMessage()));
                    } else {
                        $this->Flash->error(__('We could not reach the booking service. Please try again.'));
                    }
                    return $this->redirect(['action' => 'bookingPage', '?' => $queryParams]);
                }
            } else {
                $this->Flash->error(__('Your booking session has expired. Please select your room again.'));
                return $this->redirect(['action' => 'bookingPage', '?' => $queryParams]);
            }
        }
        $property = $quote['property'] ?? null;
        $room = $quote['room'] ?? null;
        if (!$property && $propertyId) {
            $propData = $this->apiClient->get('/properties/' . $propertyId);
            if (!empty($propData)) $property = $propData['data'] ?? $propData;
        }
        // Without a real property + room + calculation there is nothing honest to render — restart pricing.
        if (!is_array($property) || !is_array($room) || empty($calculation)) {
            $this->Flash->error(__('Your booking session has expired. Please select your room again.'));
            return $this->redirect(['action' => 'bookingPage', '?' => $queryParams]);
        }
        $this->set(compact('property','room','calculation','queryParams','quote'));
        return $this->render('/Pages/bookingpage-03');
    }

    /**
     * Release the checkout room hold.
     *
     * Called when the guest abandons checkout. The hold expires on its own
     * after 10 minutes, so this is only to release it promptly.
     */
    public function releaseRoomLock(): Response
    {
        if (!$this->getRequest()->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $session = $this->getRequest()->getSession();
        $lock = $session->read('booking_lock');
        $session->delete('booking_lock');

        $token = $this->bearerToken();
        if (!is_array($lock) || empty($lock['room_id']) || $token === '') {
            return $this->response->withType('application/json')->withStringBody(
                (string)json_encode(['status' => 'success', 'released' => false])
            );
        }

        $this->apiClient->post('/bookings/unlock', [
            'room_id'   => (int) $lock['room_id'],
            'check_in'  => $lock['check_in'] ?? null,
            'check_out' => $lock['check_out'] ?? null,
        ], ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json']);

        return $this->response->withType('application/json')->withStringBody(
            (string)json_encode(['status' => 'success', 'released' => true])
        );
    }

    /** Bearer token from the session or the persistent auth cookie. */
    private function bearerToken(): string
    {
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '' && isset($_COOKIE[\App\Service\AuthService::TOKEN_COOKIE])) {
            $token = trim((string)$_COOKIE[\App\Service\AuthService::TOKEN_COOKIE]);
            if (stripos($token, 'Bearer ') === 0) {
                $token = trim(substr($token, 7));
            }
        }
        return $token;
    }

    /**
     * POST /receipts/generate — build a downloadable e-receipt.
     *
     * Only reachable with a verified booking on the session, so the receipt is
     * always generated from stored booking data rather than URL parameters.
     */
    public function downloadReceipt(): Response
    {
        if (!$this->getRequest()->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $bookingId = trim((string)$this->getRequest()->getData('booking_id'));
        if ($bookingId === '') {
            throw new BadRequestException(__('Booking not specified.'));
        }

        $sessionEmail = (string) ($this->getRequest()->getSession()->read('User.email') ?? '');
        $booking = $this->paymentService->booking($bookingId, $sessionEmail);

        if (!is_array($booking)) {
            throw new NotFoundException(__('Booking not found.'));
        }

        $guest = is_array($booking['guest'] ?? null) ? $booking['guest'] : [];
        $property = is_array($booking['room']['property'] ?? null) ? $booking['room']['property'] : [];
        $room = is_array($booking['room'] ?? null) ? $booking['room'] : [];

        if (($booking['payment_status'] ?? '') !== 'paid') {
            throw new BadRequestException(__('A receipt is only available for paid bookings.'));
        }

        $res = $this->apiClient->post('/receipts/generate', [
            'booking_code'     => $booking['booking_code'] ?? $bookingId,
            'guest_name'       => $guest['name'] ?? null,
            'property_name'    => $property['name'] ?? null,
            'property_address' => $property['address'] ?? null,
            'room_number'      => $room['room_number'] ?? null,
            'check_in'         => $booking['check_in'] ?? null,
            'check_out'        => $booking['check_out'] ?? null,
            'total_price'      => $booking['total_price'] ?? null,
        ], ['Authorization' => 'Bearer ' . $this->bearerToken(), 'Accept' => 'application/json']);

        if (empty($res) || ($res['status'] ?? '') !== 'success') {
            $msg = $res['message'] ?? __('Could not generate your receipt.');
            $this->Flash->error(__($msg));

            return $this->response->withType('application/json')->withStringBody(json_encode([
                'status' => 'error', 'message' => $msg,
            ]));
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status'      => 'success',
            'receipt_url' => $res['receipt_url'] ?? null,
            'booking_code'=> $res['booking_code'] ?? null,
        ]));
    }


    /**
     * Booking Step 3 - Success & Confirmed Invoice
     */
    public function bookingpageSuccess()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        // Accept either identifier: my-booking links use booking_code, the
        // payment page uses the numeric booking_id. The backend resolves both.
        $bookingId = trim((string)($queryParams['booking_id'] ?? ''));
        if ($bookingId === '') {
            $bookingId = trim((string)($queryParams['booking_code'] ?? ''));
        }
        if ($bookingId === '') {
            throw new NotFoundException(__('Booking confirmation not found.'));
        }

        // A guest booking has no session, so the receipt lookup is authorised
        // by the email the booking was made with. The payment page appends it;
        // without it a guest's paid booking 404s here.
        $sessionEmail = (string) ($this->getRequest()->getSession()->read('User.email') ?? '');
        if ($sessionEmail === '') {
            $sessionEmail = trim((string)($queryParams['email'] ?? ''));
        }

        $bookingResponse = $sessionEmail !== ''
            ? $this->paymentService->booking($bookingId, $sessionEmail)
            : $this->paymentService->booking($bookingId);
        $booking = is_array($bookingResponse) ? ($bookingResponse['data'] ?? $bookingResponse) : null;
        if (!is_array($booking)) {
            throw new NotFoundException(__('This booking could not be verified.'));
        }
        $verifiedBooking = $booking;
        $paymentStatus = strtolower((string)($booking['payment_status'] ?? ''));
        $bookingStatus = strtolower((string)($booking['booking_status'] ?? ($booking['status'] ?? '')));
        if ($paymentStatus !== 'paid' || !in_array($bookingStatus, ['confirmed', 'paid', 'completed', 'success', 'successful'], true)) {
            throw new NotFoundException(__('This booking is not confirmed yet.'));
        }

        $guest = is_array($booking['guest'] ?? null) ? $booking['guest'] : [];
        $propertyDetails = is_array($booking['property'] ?? null) ? $booking['property'] : [];
        $bookingRoom = is_array($booking['room'] ?? null) ? $booking['room'] : [];
        // Confirmation details come from the verified booking record, never from URL values.
        $queryParams = array_merge($queryParams, [
            'property_id' => $booking['property_id'] ?? ($queryParams['property_id'] ?? ''),
            'room_id' => $booking['room_id'] ?? ($queryParams['room_id'] ?? ''),
            'reference' => $booking['reference'] ?? ($booking['booking_code'] ?? $bookingId),
            'guest_name' => $booking['guest_name'] ?? ($guest['name'] ?? ''),
            'guest_email' => $booking['guest_email'] ?? ($guest['email'] ?? ''),
            'guest_phone' => $booking['guest_phone'] ?? ($guest['phone'] ?? ''),
            'payment_method' => $booking['payment_method'] ?? ($booking['gateway'] ?? 'Mobile Money'),
            'payment_phone' => $booking['payment_phone'] ?? '',
            'check_in' => $booking['check_in'] ?? '',
            'check_out' => $booking['check_out'] ?? '',
            'total_amount' => $booking['total_amount'] ?? ($booking['amount'] ?? ($booking['total_price'] ?? '')),
            'property_name' => $propertyDetails['name'] ?? '',
            'property_city' => $propertyDetails['address'] ?? '',
            'room_title' => $bookingRoom['title'] ?? '',
        ]);
        $propertyId = !empty($queryParams['property_id']) ? (int)$queryParams['property_id'] : null;
        
        $property = null;
        if ($propertyId) {
            $propData = $this->apiClient->get('/properties/' . $propertyId);
            if (!empty($propData)) {
                $property = $propData['data'] ?? $propData;
            }
        }

        $this->set(compact('queryParams', 'property', 'verifiedBooking', 'guest', 'bookingRoom'));
        return $this->render('/Pages/bookingpage-success');
    }
}
