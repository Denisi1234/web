<?php
declare(strict_types=1);

namespace App\Controller\Bookings;

/**
 * BookingsQuoteTrait — Quote building and date parsing.
 */
trait BookingsQuoteTrait
{
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
        // The countdown is driven by this server-owned expires_at — never a hardcoded number.
        $resumeQuoteId = trim((string)($queryParams['quote_id'] ?? ''));
        $hadQuoteId = $resumeQuoteId !== '';
        $quoteWasExpired = false;
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
                $quoteRemaining = max(0, (int)$resumed['expires_at'] - time());
                $this->set(compact('queryParams', 'quoteRemaining') + [
                    'property' => $resumed['property'],
                    'room' => $resumed['room'],
                    'calculation' => $resumed['calculation'],
                    'quote' => $resumed,
                    'quoteError' => null,
                    'quoteRepriced' => !empty($queryParams['repriced']),
                ]);
                return $this->render('/Pages/booking-page');
            }
            // A quote_id was supplied but is missing/expired in session — fall through to
            // re-price honestly and flag it so the view can tell the guest the price was refreshed.
            $quoteWasExpired = true;
            $this->getRequest()->getSession()->delete('booking_quotes.' . $resumeQuoteId);
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
            // Stabilise the guarantee: redirect so the browser URL carries the quote_id.
            // Without this every refresh mints a brand-new quote (new expiry), making the
            // countdown meaningless. With it, refreshes resume the SAME quote and the
            // timer keeps counting down for real.
            $redirectQuery = array_merge($queryParams, [
                'quote_id' => $quote['quote_id'],
                'checkIn' => $quote['check_in'],
                'checkOut' => $quote['check_out'],
                'adults' => $quote['adults'],
                'children' => $quote['children'],
                'rooms' => $quote['rooms'],
            ]);
            if ($quoteWasExpired || $hadQuoteId === false) {
                if ($quoteWasExpired) {
                    $redirectQuery['repriced'] = '1';
                    $this->Flash->error(__('Your previous price guarantee expired, so we refreshed the live price.'));
                }
                return $this->redirect(['action' => 'bookingPage', '?' => $redirectQuery]);
            }

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

        // Safety net: reached only if the stabilising redirect above was skipped.
        // Never render a fake countdown — remaining always comes from the server quote.
        $quoteRemaining = is_array($quote) && !empty($quote['expires_at'])
            ? max(0, (int)$quote['expires_at'] - time()) : 0;
        $quoteRepriced = !empty($queryParams['repriced']) || $quoteWasExpired;
        $this->set(compact('property', 'room', 'calculation', 'queryParams', 'quote', 'quoteError', 'quoteRemaining', 'quoteRepriced'));
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
}
