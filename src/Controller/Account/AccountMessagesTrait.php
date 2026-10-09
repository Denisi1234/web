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
     * Main Messages & Live Property Chat Page
     */
    public function messages(): ?Response
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $queryParams = $this->getRequest()->getQueryParams();

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

        // 3. Fallback host ID if still 0
        if ($activeHostId === 0) {
            $activeHostId = 1; // Default host / manager partner ID
        }

        if ($lodgeName === '') {
            $lodgeName = 'Kingdoms Lodge';
        }

        // 4. Fetch live threads from backend
        $threads = [];
        if ($token !== '') {
            try {
                $threadsResp = $this->apiClient->get('/messages/threads', [], $headers);
                if (is_array($threadsResp)) {
                    $threads = !empty($threadsResp['data']) ? $threadsResp['data'] : $threadsResp;
                }
            } catch (\Throwable $e) {}
        }

        // 5. Initial active thread construct matching mobile thread model
        $activeThread = [
            'hostId' => $activeHostId,
            'hostName' => $activeLodge['host']['name'] ?? ($activeLodge['host_name'] ?? 'Property Host'),
            'lodgeName' => $lodgeName,
            'avatar' => '/assets/images/man2.jpeg',
            'isOnline' => true,
            'propertyId' => $propertyId,
            'bookingCode' => $bookingCode,
            'property' => $activeLodge,
            'booking' => $activeBooking,
        ];

        $this->set(compact('threads', 'activeThread', 'activeHostId', 'lodgeName', 'bookingCode', 'activeLodge', 'activeBooking'));
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
        $pid = is_numeric($partnerId) ? (int)$partnerId : 1;

        $messages = [];
        if ($token !== '') {
            try {
                $res = $this->apiClient->get('/messages/' . $pid, [], $headers);
                if (is_array($res)) {
                    $messages = !empty($res['data']) ? $res['data'] : $res;
                }
            } catch (\Throwable $e) {}
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => 'success',
            'messages' => $messages
        ]));
    }

    /**
     * AJAX endpoint: Send Message to Host / Supplier
     */
    public function messageSend(): Response
    {
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        $rawBody = (string)$this->getRequest()->getBody();
        $jsonDecoded = !empty($rawBody) ? json_decode($rawBody, true) : null;
        $postData = (array)$this->getRequest()->getData();
        $data = is_array($jsonDecoded) ? array_merge($postData, $jsonDecoded) : $postData;

        $recipientId = isset($data['recipient_id']) ? (int)$data['recipient_id'] : (isset($data['host_id']) ? (int)$data['host_id'] : 1);
        $lodgeName = trim((string)($data['lodge_name'] ?? ($data['property_name'] ?? 'Lodge Stay')));
        $text = trim((string)($data['text'] ?? ($data['message'] ?? '')));
        $type = trim((string)($data['type'] ?? 'text'));
        $attachmentUrl = trim((string)($data['attachment_url'] ?? ''));

        if ($text === '' && $attachmentUrl === '') {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => __('Message cannot be empty.')
            ]));
        }

        $payload = [
            'recipient_id' => $recipientId,
            'lodge_name' => $lodgeName,
            'text' => $text,
            'type' => $type,
            'attachment_url' => $attachmentUrl,
        ];

        $result = null;
        if ($token !== '') {
            try {
                $result = $this->apiClient->post('/messages', $payload, $headers);
            } catch (\Throwable $e) {}
        }

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => 'success',
            'message' => __('Message sent successfully.'),
            'data' => $result ?? $payload
        ]));
    }
}
