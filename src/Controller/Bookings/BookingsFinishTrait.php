<?php
declare(strict_types=1);

namespace App\Controller\Bookings;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * BookingsFinishTrait — Confirmation, room-lock release, receipts, and success page.
 */
trait BookingsFinishTrait
{
    public function bookingpage03()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $propertyId = !empty($queryParams['property_id']) ? (int)$queryParams['property_id'] : 0;
        $roomId = !empty($queryParams['room_id']) ? (int)$queryParams['room_id'] : null;
        // The payment step shares the SAME server quote (and its expiry) as step 1.
        // Never silently mint a new price here — an expired/missing quote must go back
        // to step 1 for an honest re-price, otherwise the guest pays a price whose
        // guarantee already lapsed.
        $quote = null;
        $quoteId = trim((string)($queryParams['quote_id'] ?? ''));
        if ($quoteId !== '') {
            $stored = $this->getRequest()->getSession()->read('booking_quotes.' . $quoteId);
            if (is_array($stored) && !empty($stored['expires_at'])) {
                if ((int)$stored['expires_at'] < time()) {
                    $this->getRequest()->getSession()->delete('booking_quotes.' . $quoteId);
                    $this->Flash->error(__('Your price guarantee expired. We sent you back to refresh the live price.'));
                    $back = array_filter([
                        'property_id' => $stored['property_id'] ?? ($propertyId ?: null),
                        'room_id' => $stored['room_id'] ?? ($roomId ?: null),
                        'checkIn' => $stored['check_in'] ?? ($queryParams['checkIn'] ?? null),
                        'checkOut' => $stored['check_out'] ?? ($queryParams['checkOut'] ?? null),
                        'adults' => $stored['adults'] ?? ($queryParams['adults'] ?? null),
                        'children' => $stored['children'] ?? ($queryParams['children'] ?? null),
                        'rooms' => $stored['rooms'] ?? ($queryParams['rooms'] ?? null),
                        'city' => $queryParams['city'] ?? ($queryParams['destination'] ?? null),
                        'repriced' => '1',
                    ], fn($v) => $v !== null && $v !== '');
                    return $this->redirect(['action' => 'bookingPage', '?' => $back]);
                }
                $quote = $stored;
                $queryParams = array_merge($queryParams, [
                    'checkIn' => $quote['check_in'] ?? $queryParams['checkIn'] ?? null,
                    'checkOut' => $quote['check_out'] ?? $queryParams['checkOut'] ?? null,
                ]);
            }
        }
        $calculation = is_array($quote) ? ($quote['calculation'] ?? null) : null;
        // Missing/unknown quote (session lost, direct link, stale bookmark): restart at
        // step 1 so a fresh authoritative quote + guarantee is created. Never fabricate.
        if (empty($quote) || empty($calculation)) {
            $this->Flash->error(__('Your booking session has expired. Please confirm your details again for a fresh price.'));
            $back = $queryParams;
            unset($back['quote_id'], $back['first_name'], $back['last_name'], $back['email'], $back['phone']);
            return $this->redirect(['action' => 'bookingPage', '?' => $back]);
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
        $quoteRemaining = max(0, (int)($quote['expires_at'] ?? 0) - time());
        $this->set(compact('property','room','calculation','queryParams','quote','quoteRemaining'));
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
            // No reference at all (bookmark, back button, typed URL) — guide
            // to the dashboard instead of dead-ending on an error page.
            try {
                \Cake\Log\Log::debug(sprintf(
                    '[bookingpageSuccess] empty reference; query=%s referer=%s',
                    json_encode($queryParams),
                    (string)$this->getRequest()->getHeaderLine('Referer')
                ));
            } catch (\Throwable $e) {
            }
            $this->Flash->error(__('Choose a booking from the list below to view its details.'));
            return $this->redirect(['controller' => 'Account', 'action' => 'myBooking']);
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
        // A message envelope (e.g. "sign in or supply the email") is an array
        // too — only a record carrying booking identity counts as verified.
        $bookingRef = is_array($booking)
            ? (string)($booking['booking_code'] ?? $booking['reference'] ?? $booking['id'] ?? $booking['booking_id'] ?? '')
            : '';
        if (!is_array($booking) || $bookingRef === '') {
            throw new NotFoundException(__('This booking could not be verified.'));
        }
        $verifiedBooking = $booking;
        $paymentStatus = strtolower((string)($booking['payment_status'] ?? ''));
        $bookingStatus = strtolower((string)($booking['booking_status'] ?? ($booking['status'] ?? '')));
        // "View details" must work for every real booking state — paid shows
        // the confirmation + invoice, anything else shows the same details
        // with an honest status banner (never a 404 for a booking that exists).
        // Only unresolvable bookings 404 (thrown above).
        $isPaid = $paymentStatus === 'paid'
            && in_array($bookingStatus, ['confirmed', 'paid', 'completed', 'success', 'successful', 'checked in', 'checked-in'], true);
        if ($isPaid) {
            // Belt-and-braces for the dashboard: the success page saw a verified
            // paid booking, so make sure /my-booking lists it even if the status
            // poll path never ran (e.g. webhook confirmed it server-side).
            $this->rememberPaidBookingForDashboard(
                (string)($booking['id'] ?? $booking['booking_id'] ?? $bookingId),
                (string)($booking['guest_email'] ?? ($booking['guest']['email'] ?? $sessionEmail)),
                [],
                $booking
            );
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

        $this->set(compact('queryParams', 'property', 'verifiedBooking', 'guest', 'bookingRoom', 'isPaid', 'paymentStatus', 'bookingStatus'));
        return $this->render('/Pages/bookingpage-success');
    }
}
