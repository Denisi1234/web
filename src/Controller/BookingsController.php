<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FastnetApiClient;
use App\Service\BookingQuoteService;
use App\Service\PaymentService;
use Cake\Http\Response;
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
        $checkIn = $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+1 day'));
        $checkOut = $queryParams['checkOut'] ?? date('Y-m-d', strtotime('+4 days'));
        $guests = (int)($queryParams['adults'] ?? 2) + (int)($queryParams['children'] ?? 0);
        $roomsCount = max(1, (int)($queryParams['rooms'] ?? 1));

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

        // 3. Authoritative price calculation via backend POST /bookings/calculate (no in-memory fallback)
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
        } catch (\Throwable $exception) {
            $quoteError = $exception->getMessage();
        }

        $this->set(compact('property', 'room', 'calculation', 'queryParams', 'quote', 'quoteError'));
        return $this->render('/Pages/booking-page');
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
            // Agoda card flow — card_number present means Step 2 payment info (no mobile money)
            if (!empty($postData['card_number']) || !empty($postData['card_holder'])) {
                $quoteId = trim((string)($postData['quote_id'] ?? ''));
                $quote = $quoteId !== '' ? $request->getSession()->read('booking_quotes.' . $quoteId) : null;
                // For demo, allow card booking even without valid quote (use query fallback)
                $propertyId = (int)($postData['property_id'] ?? ($quote['property_id'] ?? 0));
                $roomId = (int)($postData['room_id'] ?? ($quote['room_id'] ?? 0));
                $mockBookingId = 'AGODA-' . time();
                // Store mock paid booking for success page demo
                $request->getSession()->write('mock_card_booking.' . $mockBookingId, [
                    'id' => $mockBookingId, 'booking_code' => $mockBookingId,
                    'property_id' => $propertyId, 'room_id' => $roomId,
                    'guest_name' => trim((string)($postData['card_holder'] ?? 'Mudrick Mahenge')),
                    'payment_status'=>'paid','booking_status'=>'confirmed','total_amount'=>2178.24,
                ]);
                return $this->redirect(['action' => 'bookingpageSuccess', '?' => ['booking_id' => $mockBookingId, 'mock_card'=>1]]);
            }
            $quoteId = trim((string)($postData['quote_id'] ?? ''));
            $quote = $quoteId !== '' ? $request->getSession()->read('booking_quotes.' . $quoteId) : null;
            if (!is_array($quote) || empty($quote['expires_at']) || (int)$quote['expires_at'] < time()) {
                $this->Flash->error(__('Your room quote has expired. Please select the room again.'));
                return $this->redirect(['action' => 'bookingPage']);
            }
            if (!$this->paymentService->isConfigured()) {
                $this->Flash->error(__('Online payments are not configured yet. Please try again later.'));
                return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
            }
            
            $firstName = trim($postData['first_name'] ?? '');
            $lastName = trim($postData['last_name'] ?? '');
            $fullName = trim($firstName . ' ' . $lastName);
            if (empty($fullName)) $fullName = 'Guest Traveler';

            $paymentMethod = trim($postData['payment_method'] ?? 'vodacom');
            $paymentPhone = trim($postData['payment_phone'] ?? ($postData['phone'] ?? ''));
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

                $paymentResult = $this->paymentService->initiate([
                    'booking_id' => $bookingId,
                    'amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_phone' => $paymentPhone,
                    'account_name' => $paymentAccountName,
                ]);
                $paymentData = is_array($paymentResult) ? ($paymentResult['data'] ?? $paymentResult) : [];
                $paymentId = (string)($paymentData['id'] ?? ($paymentData['payment_id'] ?? ($paymentData['transaction_id'] ?? '')));
                if ($paymentId === '') {
                    $this->Flash->error(__('We could not start the payment request. Please try again.'));
                    return $this->redirect(['action' => 'bookingPage', '?' => ['quote_id' => $quoteId]]);
                }

                $request->getSession()->write('pending_payments.' . $paymentId, [
                    'booking_id' => $bookingId,
                    'created_at' => time(),
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

    public function paymentStatus(): Response
    {
        $paymentId = trim((string)$this->getRequest()->getQuery('payment_id', ''));
        $pending = $paymentId !== '' ? $this->getRequest()->getSession()->read('pending_payments.' . $paymentId) : null;
        if (!is_array($pending)) {
            return $this->response->withStatus(404)->withType('application/json')->withStringBody(json_encode(['status' => 'expired']));
        }

        $result = $this->paymentService->status($paymentId, (string)$pending['booking_id']);
        $data = is_array($result) ? ($result['data'] ?? $result) : [];
        $status = strtolower((string)($data['status'] ?? ($data['payment_status'] ?? ($data['transactionStatus'] ?? ($data['paymentStatus'] ?? 'pending')))));
        $normalized = match ($status) {
            'paid', 'success', 'successful', 'completed', 'confirmed' => 'paid',
            'failed', 'cancelled', 'canceled', 'declined' => 'failed',
            'expired', 'timeout', 'timed_out' => 'expired',
            default => 'pending',
        };
        if ($normalized === 'paid') {
            $this->getRequest()->getSession()->delete('pending_payments.' . $paymentId);
        }
        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => $normalized,
            'booking_id' => $pending['booking_id'],
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
        $property = null;
        $room = null;
        $calculation = $quote['calculation'] ?? null;
        if ($propertyId) {
            $propData = $this->apiClient->get('/properties/' . $propertyId);
            if (!empty($propData)) $property = $propData['data'] ?? $propData;
        }
        if (!$property && $quote) $property = $quote['property'] ?? null;
        if (!$room && $quote) $room = $quote['room'] ?? null;
        if (!$property) $property = ['id'=>$propertyId,'name'=>'Divi Village Golf and Beach Resort','city'=>'Oranjestad','star_rating'=>4,'rating'=>8.4,'review_count'=>737,'address'=>'J.E. Irausquin Blvd 93, Oranjestad, Aruba'];
        if (!$room) $room = ['id'=>$roomId,'name'=>'Golf Villa One Bedroom Suite','size'=>'78 m²','max_occupancy'=>2,'bed_configuration'=>'1 king bed and 1 sofa bed','price'=>275];
        // fallback calculation if none
        if (!$calculation) {
            $nights = max(1, (int)round((strtotime($queryParams['checkOut'] ?? date('Y-m-d',strtotime('+6 days'))) - strtotime($queryParams['checkIn'] ?? date('Y-m-d'))) / 86400));
            if ($nights <1) $nights=6;
            $roomPrice = (float)($room['price'] ?? 275);
            $orig = 5041.00;
            $roomTotal = 1662.49;
            $taxes = 515.75;
            $total = 2178.24;
            $calculation = ['original_price'=>$orig,'subtotal'=>$roomTotal,'taxes'=>$taxes,'total_amount'=>$total,'nights'=>$nights];
        }
        $this->set(compact('property','room','calculation','queryParams','quote'));
        return $this->render('/Pages/bookingpage-03');
    }

    /**
     * Booking Step 3 - Success & Confirmed Invoice
     */
    public function bookingpageSuccess()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $bookingId = trim((string)($queryParams['booking_id'] ?? ''));
        if ($bookingId === '') {
            throw new NotFoundException(__('Booking confirmation not found.'));
        }
        // Mock card booking for Agoda demo (no backend)
        if (!empty($queryParams['mock_card']) || str_starts_with($bookingId,'AGODA-')) {
            $mock = $this->getRequest()->getSession()->read('mock_card_booking.' . $bookingId);
            if (is_array($mock)) {
                $queryParams = array_merge($queryParams, [
                    'reference'=>$mock['booking_code'],'guest_name'=>$mock['guest_name'],
                    'property_id'=>$mock['property_id'],'room_id'=>$mock['room_id'],
                    'payment_status'=>'paid','booking_status'=>'confirmed','total_amount'=>$mock['total_amount'],
                ]);
                $property = ['id'=>$mock['property_id'],'name'=>'Divi Village Golf and Beach Resort'];
                $this->set(compact('queryParams','property'));
                return $this->render('/Pages/bookingpage-success');
            }
        }

        $bookingResponse = $this->paymentService->booking($bookingId);
        $booking = is_array($bookingResponse) ? ($bookingResponse['data'] ?? $bookingResponse) : null;
        if (!is_array($booking)) {
            throw new NotFoundException(__('This booking could not be verified.'));
        }
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

        $this->set(compact('queryParams', 'property'));
        return $this->render('/Pages/bookingpage-success');
    }
}
