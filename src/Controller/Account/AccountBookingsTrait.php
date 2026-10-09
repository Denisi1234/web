<?php
declare(strict_types=1);

namespace App\Controller\Account;

use Cake\Http\Response;

/**
 * AccountBookingsTrait — Booking cancel/find, payments, and wishlists.
 */
trait AccountBookingsTrait
{
    public function cancelBooking(): Response
    {
        $isJson = $this->getRequest()->is('json') || $this->getRequest()->getHeaderLine('Content-Type') === 'application/json';
        $data = $isJson ? (array)json_decode((string)$this->getRequest()->getBody(), true) : (array)$this->getRequest()->getData();
        $bookingId = trim((string)($data['booking_id'] ?? ''));
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        if ($bookingId === '') {
            $msg = __('Invalid booking ID.');
            if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message' => $msg]));
            $this->Flash->error($msg);
            return $this->redirect(['action' => 'myBooking']);
        }

        $session = $this->getRequest()->getSession();
        $user = (array)($session->read('User') ?? ($session->read('userProfile') ?? []));
        $userEmail = strtolower(trim((string)($user['email'] ?? '')));
        $endpoint = '/bookings/' . rawurlencode($bookingId);
        if ($userEmail !== '') {
            $endpoint .= '?email=' . rawurlencode($userEmail);
        }

        $result = $this->apiClient->delete($endpoint, $headers);
        $status = 200;
        if (is_array($result) && isset($result['_status']) && is_int($result['_status'])) {
            $status = $result['_status'];
            unset($result['_status']);
        }
        
        // Also update in session if present
        $sessionBookings = $session->read('user_bookings') ?? [];
        if (is_array($sessionBookings)) {
            foreach ($sessionBookings as &$sb) {
                if ((string)($sb['id'] ?? '') === $bookingId || (string)($sb['booking_code'] ?? '') === $bookingId) {
                    $sb['status'] = 'Cancelled';
                    $sb['booking_status'] = 'Cancelled';
                }
            }
            $session->write('user_bookings', $sessionBookings);
        }

        $bookingsList = $session->read('bookings') ?? [];
        if (is_array($bookingsList)) {
            foreach ($bookingsList as &$sb) {
                if ((string)($sb['id'] ?? '') === $bookingId || (string)($sb['booking_code'] ?? '') === $bookingId) {
                    $sb['status'] = 'Cancelled';
                    $sb['booking_status'] = 'Cancelled';
                }
            }
            $session->write('bookings', $bookingsList);
        }

        $msg = (is_array($result) && !empty($result['message'])) ? (string)$result['message'] : __('Stay cancelled. The property has been notified.');
        if ($isJson) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'status' => ($status >= 200 && $status < 300) ? 'success' : 'error',
                'message' => $msg,
            ]));
        }
        $this->Flash->success($msg);
        return $this->redirect(['action' => 'myBooking']);
    }

    /**
     * Real date changes (quote → apply), proxied to the backend reschedule
     * endpoints with the portal token. Responses (including 4xx/5xx bodies)
     * pass straight through so the UI shows the server's own message.
     */
    public function rescheduleQuote(): Response
    {
        return $this->forwardReschedule('/reschedule/quote');
    }

    public function rescheduleApply(): Response
    {
        return $this->forwardReschedule('/reschedule');
    }

    private function forwardReschedule(string $suffix): Response
    {
        $data = json_decode((string)$this->getRequest()->getBody(), true) ?: (array)$this->getRequest()->getData();
        $bookingId = trim((string)($data['booking_id'] ?? ''));
        $checkIn = trim((string)($data['check_in'] ?? ''));
        $checkOut = trim((string)($data['check_out'] ?? ''));
        if ($bookingId === '' || $checkIn === '' || $checkOut === '') {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message' => 'Booking and new dates are required.']));
        }
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $res = $this->apiClient->post(
            '/bookings/' . rawurlencode($bookingId) . $suffix,
            ['check_in' => $checkIn, 'check_out' => $checkOut],
            $headers
        );
        $status = 200;
        if (is_array($res) && isset($res['_status']) && is_int($res['_status'])) {
            $status = $res['_status'];
            unset($res['_status']);
        }
        if (!is_array($res)) {
            return $this->response->withStatus(502)->withType('application/json')->withStringBody(json_encode(['message' => 'Service unavailable. Please try again.']));
        }
        return $this->response->withStatus($status)->withType('application/json')->withStringBody((string)json_encode($res));
    }

    public function findBooking(): Response
    {
        $data = json_decode((string)$this->getRequest()->getBody(), true) ?: $this->getRequest()->getData();
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $bookingCode = trim((string)($data['booking_number'] ?? ($data['booking_code'] ?? '')));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $bookingCode === '') {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message' => 'Enter a valid email and booking number.']));
        }

        // Try direct booking lookup
        $found = null;
        $res = $this->apiClient->get('/bookings', ['booking_code' => $bookingCode]);
        if (is_array($res) && !empty($res) && empty($res['_status']) && empty($res['message'])) {
            $b = isset($res[0]) ? $res[0] : ($res['data'][0] ?? $res);
            if (is_array($b) && !empty($b['id'])) {
                $bEmail = strtolower(trim((string)($b['guest']['email'] ?? '')));
                if ($bEmail === '' || hash_equals($bEmail, $email)) {
                    $found = $b;
                }
            }
        }

        // Try payments status lookup
        if (!$found) {
            $payRes = $this->apiClient->get('/payments/status/' . rawurlencode($bookingCode));
            $pData = is_array($payRes) ? ($payRes['data'] ?? $payRes) : [];
            if (!empty($pData) && empty($pData['_status']) && empty($pData['message'])) {
                $guestEmail = strtolower(trim((string)($pData['guest']['email'] ?? $pData['guest_email'] ?? '')));
                if ($guestEmail === '' || hash_equals($guestEmail, $email)) {
                    $found = $pData;
                }
            }
        }

        if (!$found) {
            return $this->response->withStatus(404)->withType('application/json')->withStringBody(json_encode(['message' => 'We could not find a booking matching those details.']));
        }

        return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 'success', 'booking' => $found]));
    }

    public function paymentDetail()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/payment-detail');
    }

    public function myWishlists()
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $userProfile = $this->authService->getPersonalDetails($token);
        $wishlists = [];
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        // Real backend — GET /wishlist returns property objects
        $res = $this->apiClient->get('/wishlist', [], $headers);
        if (is_array($res)) {
            if (!empty($res['data']) && is_array($res['data'])) $wishlists = $res['data'];
            elseif (isset($res[0])) $wishlists = $res;
            elseif (!empty($res) && isset($res['id'])) $wishlists = [$res];
        }
        $this->set(compact('userProfile', 'wishlists'));
        return $this->render('/Pages/my-wishlists');
    }

    public function wishlistAdd(): \Cake\Http\Response
    {
        $this->request->allowMethod(['post']);
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $data = json_decode((string)$this->getRequest()->getBody(), true) ?: $this->getRequest()->getData();
        $pid = (int)($data['property_id'] ?? 0);
        if ($pid <= 0) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>'Invalid property']));
        $res = $this->apiClient->post('/wishlist', ['property_id'=>$pid], $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? ['ok'=>true]));
    }

    public function wishlistRemove(string $id): \Cake\Http\Response
    {
        $this->request->allowMethod(['delete']);
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $res = $this->apiClient->delete('/wishlist/'.rawurlencode($id), $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? ['ok'=>true]));
    }

    public function wishlistLists(): \Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization'=>'Bearer '.$token] : [];
        $res = $this->apiClient->get('/wishlist-lists', [], $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? []));
    }

    public function wishlistListCreate(): \Cake\Http\Response
    {
        $this->request->allowMethod(['post']);
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $data = json_decode((string)$this->getRequest()->getBody(), true) ?: $this->getRequest()->getData();
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>'Name required']));
        $res = $this->apiClient->post('/wishlist-lists', ['name'=>$name], $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? ['ok'=>true]));
    }
}
