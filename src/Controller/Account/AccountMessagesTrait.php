<?php
declare(strict_types=1);

namespace App\Controller\Account;

use Cake\Http\Response;

/**
 * AccountMessagesTrait — Guest & Host Messaging matching Mobile App experience.
 */
trait AccountMessagesTrait
{
    /**
     * Guest inbox + property chat. Sign-in required — anonymous visitors
     * bounce to login with a return address. No fake threads, names,
     * avatars or presence: everything rendered comes from the backend or
     * the booking/property the guest arrived from.
     */
    public function messages(): ?Response
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $queryParams = $this->getRequest()->getQueryParams();

        if ($token === '' || $session->read('is_logged_out')) {
            $target = '/messages';
            $qs = http_build_query(array_filter([
                'host_id' => $queryParams['host_id'] ?? null,
                'property_id' => $queryParams['property_id'] ?? null,
                'lodge_name' => $queryParams['lodge_name'] ?? ($queryParams['property_name'] ?? null),
                'booking_code' => $queryParams['booking_code'] ?? ($queryParams['code'] ?? null),
            ]));
            if ($qs !== '') {
                $target .= '?' . $qs;
            }

            return $this->redirect('/login?redirect=' . urlencode($target));
        }

        $activeHostId = isset($queryParams['host_id']) && is_numeric($queryParams['host_id']) ? (int)$queryParams['host_id'] : 0;
        $propertyId = isset($queryParams['property_id']) && is_numeric($queryParams['property_id']) ? (int)$queryParams['property_id'] : 0;
        $lodgeName = trim((string)($queryParams['lodge_name'] ?? ($queryParams['property_name'] ?? '')));
        $bookingCode = trim((string)($queryParams['booking_code'] ?? ($queryParams['code'] ?? '')));
        $activeLodge = null;
        $activeBooking = null;

        // 1. Resolve booking if booking code provided
        if ($bookingCode !== '') {
            $sessionBookings = array_merge(
                (array)($session->read('user_bookings_cache') ?? []),
                (array)($session->read('user_bookings') ?? []),
                (array)($session->read('bookings') ?? [])
            );
            foreach ($sessionBookings as $sb) {
                if (!is_array($sb)) continue;
                $sbCode = (string)($sb['booking_code'] ?? ($sb['booking_number'] ?? ($sb['id'] ?? '')));
                if ($sbCode === $bookingCode) {
                    $activeBooking = $sb;
                    if ($propertyId === 0 && !empty($sb['property_id'])) {
                        $propertyId = (int)$sb['property_id'];
                    }
                    if ($lodgeName === '' && !empty($sb['property_name'])) {
                        $lodgeName = (string)$sb['property_name'];
                    }
                    break;
                }
            }
        }

        // 2. Resolve property and host if property_id provided
        if ($propertyId > 0) {
            $propResp = $this->apiClient->get('/properties/' . $propertyId);
            $propData = !empty($propResp['data']) ? $propResp['data'] : $propResp;
            if (is_array($propData) && !empty($propData['name'])) {
                $activeLodge = $propData;
                if ($lodgeName === '') {
                    $lodgeName = (string)$propData['name'];
                }
                if ($activeHostId === 0 && !empty($propData['host_id'])) {
                    $activeHostId = (int)$propData['host_id'];
                }
            }
        }

        // 3. Real threads from backend (also the fallback for choosing the
        // active conversation — never a hardcoded host id).
        $threads = [];
        try {
            $threadsResp = $this->apiClient->get('/messages/threads', [], $headers);
            if (is_array($threadsResp)) {
                $threads = !empty($threadsResp['data']) ? $threadsResp['data'] : $threadsResp;
            }
        } catch (\Throwable $e) {
        }
        if (!is_array($threads)) {
            $threads = [];
        }
        $threads = array_values(array_filter($threads, fn($t) => is_array($t) && !empty($t['partner_id'])));

        if ($activeHostId === 0 && !empty($threads)) {
            $activeHostId = (int)$threads[0]['partner_id'];
            if ($lodgeName === '' && !empty($threads[0]['lodge_name'])) {
                $lodgeName = (string)$threads[0]['lodge_name'];
            }
        }

        $sessionUser = $session->read('User');
        $currentUserId = (int)(is_array($sessionUser) ? ($sessionUser['id'] ?? 0) : 0);

        // 4. Never message yourself (e.g. a host opening their own link).
        $isSelfChat = $currentUserId > 0 && $activeHostId === $currentUserId;

        // 5. Initial history for the active conversation, if any.
        $initialMessages = [];
        $activePartnerName = '';
        $activePartnerRole = '';
        if ($activeHostId > 0 && !$isSelfChat) {
            foreach ($threads as $t) {
                if ((int)$t['partner_id'] === $activeHostId) {
                    $activePartnerName = (string)($t['partner_name'] ?? '');
                    $activePartnerRole = (string)($t['partner_role'] ?? '');
                    if ($lodgeName === '' && !empty($t['lodge_name'])) {
                        $lodgeName = (string)$t['lodge_name'];
                    }
                    break;
                }
            }
            try {
                $histResp = $this->apiClient->get('/messages/' . $activeHostId, [], $headers);
                if (is_array($histResp)) {
                    $initialMessages = !empty($histResp['data']) ? $histResp['data'] : $histResp;
                }
            } catch (\Throwable $e) {
            }
            if (!is_array($initialMessages)) {
                $initialMessages = [];
            }
            $initialMessages = array_values(array_filter($initialMessages, 'is_array'));
        }

        $hostName = $activePartnerName !== '' ? $activePartnerName : 'Property Host';

        $activeThread = [
            'hostId' => $activeHostId,
            'hostName' => $hostName,
            'partnerRole' => $activePartnerRole,
            'lodgeName' => $lodgeName,
            'isSelfChat' => $isSelfChat,
            'propertyId' => $propertyId,
            'bookingCode' => $bookingCode,
            'property' => $activeLodge,
            'booking' => $activeBooking,
        ];

        $this->set(compact(
            'threads',
            'activeThread',
            'activeHostId',
            'lodgeName',
            'bookingCode',
            'activeLodge',
            'activeBooking',
            'initialMessages',
            'currentUserId'
        ));

        return $this->render('/Pages/messages');
    }

    /**
     * AJAX endpoint: Get Threads List
     */
    public function messageThreads(): Response
    {
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        
        $threads = [];
        if ($token !== '') {
            try {
                $res = $this->apiClient->get('/messages/threads', [], $headers);
                $threads = is_array($res) ? (!empty($res['data']) ? $res['data'] : $res) : [];
            } catch (\Throwable $e) {}
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => 'success',
            'threads' => $threads
        ]));
    }

    /**
     * AJAX endpoint: Get Message History for a partner
     */
    public function messageHistory(?string $partnerId = null): Response
    {
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $pid = is_numeric($partnerId) ? (int)$partnerId : 0;
        if ($pid <= 0) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Conversation not found.')
            ]));
        }

        $messages = [];
        if ($token !== '') {
            try {
                $res = $this->apiClient->get('/messages/' . $pid, [], $headers);
                if (is_array($res)) {
                    $messages = !empty($res['data']) ? $res['data'] : $res;
                }
            } catch (\Throwable $e) {}
        }
        if (!is_array($messages)) {
            $messages = [];
        }

        $sessionUser = $this->getRequest()->getSession()->read('User');
        $userId = (int)(is_array($sessionUser) ? ($sessionUser['id'] ?? 0) : 0);

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => 'success',
            'user_id' => $userId,
            'messages' => array_values(array_filter($messages, 'is_array'))
        ]));
    }

    /**
     * AJAX endpoint: Send Message to Host. Forwards backend validation
     * honestly — 401 signed out, 422 rejected, 201 saved.
     */
    public function messageSend(): Response
    {
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        if ($token === '' || $this->getRequest()->getSession()->read('is_logged_out')) {
            return $this->response->withStatus(401)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Sign in to send messages.'),
                'loginUrl' => '/login?redirect=' . urlencode('/messages'),
            ]));
        }

        $rawBody = (string)$this->getRequest()->getBody();
        $jsonDecoded = !empty($rawBody) ? json_decode($rawBody, true) : null;
        $postData = (array)$this->getRequest()->getData();
        $data = is_array($jsonDecoded) ? array_merge($postData, $jsonDecoded) : $postData;

        $recipientId = isset($data['recipient_id']) ? (int)$data['recipient_id'] : (isset($data['host_id']) ? (int)$data['host_id'] : 0);
        $lodgeName = trim((string)($data['lodge_name'] ?? ($data['property_name'] ?? '')));
        $text = trim((string)($data['text'] ?? ($data['message'] ?? '')));

        if ($recipientId <= 0) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Choose a conversation first.')
            ]));
        }
        if ($text === '') {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Message cannot be empty.')
            ]));
        }
        if (mb_strlen($text) > 2000) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Messages must be 2000 characters or fewer.')
            ]));
        }

        $sessionUser = $this->getRequest()->getSession()->read('User');
        $myId = (int)(is_array($sessionUser) ? ($sessionUser['id'] ?? 0) : 0);
        if ($myId > 0 && $recipientId === $myId) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('You cannot message yourself.')
            ]));
        }

        // The backend persists text only (no attachment/type columns) — send
        // exactly what is stored so the UI never implies richer messaging.
        // reply_to_id carries a WhatsApp-style quote when replying.
        $payload = [
            'recipient_id' => $recipientId,
            'lodge_name' => $lodgeName !== '' ? $lodgeName : 'Property chat',
            'text' => $text,
        ];
        if (isset($data['reply_to_id']) && (int)$data['reply_to_id'] > 0) {
            $payload['reply_to_id'] = (int)$data['reply_to_id'];
        }

        try {
            $result = $this->apiClient->post('/messages', $payload, $headers);
        } catch (\Throwable $e) {
            $result = null;
        }

        if (!is_array($result)) {
            return $this->response->withStatus(502)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Could not reach the server. Please try again.')
            ]));
        }
        $status = (int)($result['_status'] ?? 0);
        if ($status === 401) {
            return $this->response->withStatus(401)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Your session has expired. Please sign in again.'),
                'loginUrl' => '/login?redirect=' . urlencode('/messages'),
            ]));
        }
        if ($status >= 400 || !empty($result['_error'])) {
            return $this->response
                ->withStatus($status >= 400 ? $status : 422)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => (string)($result['message'] ?? __('Could not send your message. Please try again.')),
                ]));
        }

        unset($result['_status']);

        return $this->response->withStatus(201)->withType('application/json')->withStringBody(json_encode([
            'status' => 'success',
            'message' => __('Message sent successfully.'),
            'data' => $result
        ]));
    }
}
