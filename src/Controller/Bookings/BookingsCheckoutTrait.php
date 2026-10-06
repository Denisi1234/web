<?php
declare(strict_types=1);

namespace App\Controller\Bookings;

/**
 * BookingsCheckoutTrait — Checkout POST handling and pending-payment tracking.
 */
trait BookingsCheckoutTrait
{
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
            if (!empty($apiResult['message']) && empty($apiResult['id']) && empty($apiResult['booking_id']) && empty($apiResult['data']) && empty($apiResult['booking'])) {
                // 422 validation or generic error without booking data
                $msg = $apiResult['message'];
                if (stripos($msg, 'not available') !== false) $msg = 'This room was just booked for these dates. Please choose another room.';
                $this->Flash->error(__($msg));
                return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
            }

            if (!empty($apiResult) && (!empty($apiResult['id']) || !empty($apiResult['booking_id']) || !empty($apiResult['data']) || !empty($apiResult['booking']))) {
                // Backend nests the record under booking:{...}; accept all shapes.
                $bData = $apiResult['data'] ?? $apiResult['booking'] ?? $apiResult;
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
                // Dashboard resume index: if the guest closes the payment page
                // mid-flow, /my-booking can still offer "Complete payment".
                $this->indexPendingPayment($paymentId, $bookingCode);
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
            // Lost/expired payment session (timeout, another device, session
            // store restart) used to dead-end on an error page. Recover via
            // the dashboard instead: the email + reference lookup there can
            // still find and resume the booking.
            $this->Flash->error(__('That payment session has expired. Find your booking below to continue.'));
            return $this->redirect(['controller' => 'Account', 'action' => 'myBooking']);
        }
        $this->set(compact('paymentId', 'pending'));
        return $this->render('/Pages/booking-payment');
    }

    /**
     * Dashboard resume index for in-flight mobile-money payments.
     * Lets /my-booking offer "Complete payment" when the guest closed the
     * payment page. Entries are pruned on settle and by age on read.
     */
    private function indexPendingPayment(string $paymentId, string $bookingCode): void
    {
        if ($paymentId === '') return;
        try {
            $session = $this->getRequest()->getSession();
            $index = $session->read('user_pending_payments');
            if (!is_array($index)) $index = [];
            $index = array_values(array_filter($index, fn($e) => is_array($e) && ($e['payment_id'] ?? '') !== $paymentId));
            $index[] = ['payment_id' => $paymentId, 'booking_code' => $bookingCode, 'created_at' => time()];
            $session->write('user_pending_payments', array_slice($index, -10));
        } catch (\Throwable $e) {
        }
    }

    private function forgetPendingPayment(string $paymentId): void
    {
        if ($paymentId === '') return;
        try {
            $session = $this->getRequest()->getSession();
            $index = $session->read('user_pending_payments');
            if (!is_array($index)) return;
            $session->write('user_pending_payments', array_values(array_filter(
                $index,
                fn($e) => is_array($e) && ($e['payment_id'] ?? '') !== $paymentId
            )));
        } catch (\Throwable $e) {
        }
    }

    /**
     * Persist a paid booking into the dashboard session so /my-booking shows
     * it immediately — never dependent on backend list timing, token state,
     * or which email the guest paid with. Backend record wins; the local
     * pending entry only fills blanks. Never throws.
     */
    private function rememberPaidBookingForDashboard(string $bookingId, string $email, array $fallback = [], ?array $prefetched = null): void
    {
        if ($bookingId === '') return;
        $b = $prefetched;
        if (!is_array($b)) {
            try {
                $rec = $this->paymentService->booking($bookingId, $email);
            } catch (\Throwable $e) {
                $rec = null;
            }
            $b = is_array($rec) ? ($rec['data'] ?? $rec) : null;
        }
        if (!is_array($b)) $b = [];
        $code = (string)($b['booking_code'] ?? $b['reference'] ?? ($fallback['booking_code'] ?? $bookingId));
        $record = array_merge([
            'id' => $bookingId,
            'booking_code' => $code,
            'guest_email' => $email !== '' ? $email : ($fallback['guest_email'] ?? ''),
            'total_price' => $fallback['amount'] ?? 0,
            'status' => 'Confirmed',
            'payment_status' => 'paid',
            '_dashboard_at' => time(),
        ], array_filter($b, fn($v) => $v !== null && $v !== ''));
        if (empty($record['booking_code'])) $record['booking_code'] = $bookingId;
        // Keep explicit paid markers even if the backend shape lacks them.
        if (empty($record['payment_status'])) $record['payment_status'] = 'paid';
        if (empty($record['status'])) $record['status'] = 'Confirmed';
        try {
            $session = $this->getRequest()->getSession();
            $stored = $session->read('user_bookings');
            if (!is_array($stored)) $stored = [];
            $keyOf = fn($r) => (string)($r['booking_code'] ?? $r['id'] ?? '');
            $stored = array_values(array_filter($stored, fn($r) => is_array($r) && $keyOf($r) !== '' && $keyOf($r) !== $code && $keyOf($r) !== $bookingId));
            $stored[] = $record;
            $session->write('user_bookings', array_slice($stored, -25));
        } catch (\Throwable $e) {
        }
    }
}
