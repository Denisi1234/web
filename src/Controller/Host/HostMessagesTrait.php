<?php
declare(strict_types=1);

namespace App\Controller\Host;

use Cake\Http\Response;

/**
 * HostMessagesTrait — Host inbox. Same backend conversation store as the
 * guest inbox (threads/history/send), scoped by the caller's token: a host
 * only ever sees threads they participate in. Read-only except sending
 * replies; no canned content anywhere.
 */
trait HostMessagesTrait
{
    public function inbox()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $sessionUser = $this->sessionUser();
        $myId = (int)($sessionUser['id'] ?? 0);

        $q = $this->getRequest()->getQueryParams();
        $partnerId = isset($q['partner_id']) && is_numeric($q['partner_id']) ? (int)$q['partner_id'] : 0;
        $lodgeName = trim((string)($q['lodge_name'] ?? ''));

        $threads = [];
        if ($this->rawToken() !== '') {
            $this->releaseSession();
            try {
                $res = $this->apiClient->get('/messages/threads', [], $headers);
                if (is_array($res)) {
                    $threads = !empty($res['data']) ? $res['data'] : $res;
                }
            } catch (\Throwable $e) {
            }
        }
        if (!is_array($threads)) {
            $threads = [];
        }
        $threads = array_values(array_filter($threads, fn($t) => is_array($t) && !empty($t['partner_id'])));

        if ($partnerId === 0 && !empty($threads)) {
            $partnerId = (int)$threads[0]['partner_id'];
        }

        $partnerName = '';
        $partnerRole = '';
        if ($partnerId > 0) {
            foreach ($threads as $t) {
                if ((int)$t['partner_id'] === $partnerId) {
                    $partnerName = (string)($t['partner_name'] ?? '');
                    $partnerRole = (string)($t['partner_role'] ?? '');
                    if ($lodgeName === '' && !empty($t['lodge_name'])) {
                        $lodgeName = (string)$t['lodge_name'];
                    }
                    break;
                }
            }
        }

        $initialMessages = [];
        if ($partnerId > 0 && !($myId > 0 && $partnerId === $myId)) {
            $this->releaseSession();
            try {
                $hist = $this->apiClient->get('/messages/' . $partnerId, [], $headers);
                if (is_array($hist)) {
                    $initialMessages = !empty($hist['data']) ? $hist['data'] : $hist;
                }
            } catch (\Throwable $e) {
            }
            if (!is_array($initialMessages)) {
                $initialMessages = [];
            }
            $initialMessages = array_values(array_filter($initialMessages, 'is_array'));
        }

        $active = [
            'partner_id' => $partnerId,
            'partner_name' => $partnerName,
            'partner_role' => $partnerRole,
            'lodge_name' => $lodgeName,
            'booking_code' => '',
            'booking' => [],
            'property' => [],
            'is_self' => $myId > 0 && $partnerId === $myId,
        ];

        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'threads', 'active', 'initialMessages', 'backendError'));
        $this->set('currentUserId', $myId);
        $this->set('isHostView', true);

        return $this->render('/Pages/host-messages');
    }

    public function inboxThreads(): Response
    {
        $threads = [];
        if ($this->rawToken() !== '') {
            $this->releaseSession();
            try {
                $res = $this->apiClient->get('/messages/threads', [], $this->hostHeaders());
                if (is_array($res)) {
                    $threads = !empty($res['data']) ? $res['data'] : $res;
                }
            } catch (\Throwable $e) {
            }
        }
        if (!is_array($threads)) {
            $threads = [];
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'status' => 'success',
            'threads' => array_values(array_filter($threads, 'is_array')),
        ]));
    }

    public function inboxHistory(?string $partnerId = null): Response
    {
        $pid = is_numeric($partnerId) ? (int)$partnerId : 0;
        if ($pid <= 0) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => __('Conversation not found.'),
            ]));
        }
        $messages = [];
        if ($this->rawToken() !== '') {
            $this->releaseSession();
            try {
                $res = $this->apiClient->get('/messages/' . $pid, [], $this->hostHeaders());
                if (is_array($res)) {
                    $messages = !empty($res['data']) ? $res['data'] : $res;
                }
            } catch (\Throwable $e) {
            }
        }
        if (!is_array($messages)) {
            $messages = [];
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'status' => 'success',
            'user_id' => (int)($this->sessionUser()['id'] ?? 0),
            'messages' => array_values(array_filter($messages, 'is_array')),
        ]));
    }

    public function inboxSend(): Response
    {
        if ($this->rawToken() === '' || $this->getRequest()->getSession()->read('is_logged_out')) {
            return $this->response->withStatus(401)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => __('Sign in to send messages.'),
                'loginUrl' => '/login?redirect=' . urlencode('/host/messages'),
            ]));
        }

        $rawBody = (string)$this->getRequest()->getBody();
        $jsonDecoded = !empty($rawBody) ? json_decode($rawBody, true) : null;
        $postData = (array)$this->getRequest()->getData();
        $data = is_array($jsonDecoded) ? array_merge($postData, $jsonDecoded) : $postData;

        $recipientId = (int)($data['recipient_id'] ?? ($data['partner_id'] ?? 0));
        $lodgeName = trim((string)($data['lodge_name'] ?? ''));
        $text = trim((string)($data['text'] ?? ($data['message'] ?? '')));

        if ($recipientId <= 0) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => __('Choose a conversation first.'),
            ]));
        }
        if ($text === '' || mb_strlen($text) > 2000) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => $text === '' ? __('Message cannot be empty.') : __('Messages must be 2000 characters or fewer.'),
            ]));
        }
        $myId = (int)($this->sessionUser()['id'] ?? 0);
        if ($myId > 0 && $recipientId === $myId) {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => __('You cannot message yourself.'),
            ]));
        }

        try {
            $sendPayload = [
                'recipient_id' => $recipientId,
                'lodge_name' => $lodgeName !== '' ? $lodgeName : 'Property chat',
                'text' => $text,
            ];
            if (isset($data['reply_to_id']) && (int)$data['reply_to_id'] > 0) {
                $sendPayload['reply_to_id'] = (int)$data['reply_to_id'];
            }
            $result = $this->apiClient->post('/messages', $sendPayload, $this->hostHeaders());
        } catch (\Throwable $e) {
            $result = null;
        }

        if (!is_array($result)) {
            return $this->response->withStatus(502)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => __('Could not reach the server. Please try again.'),
            ]));
        }
        $status = (int)($result['_status'] ?? 0);
        if ($status === 401) {
            return $this->response->withStatus(401)->withType('application/json')->withStringBody((string)json_encode([
                'status' => 'error',
                'message' => __('Your session has expired. Please sign in again.'),
                'loginUrl' => '/login?redirect=' . urlencode('/host/messages'),
            ]));
        }
        if ($status >= 400 || !empty($result['_error'])) {
            return $this->response
                ->withStatus($status >= 400 ? $status : 422)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'status' => 'error',
                    'message' => (string)($result['message'] ?? __('Could not send your message. Please try again.')),
                ]));
        }
        unset($result['_status']);

        return $this->response->withStatus(201)->withType('application/json')->withStringBody((string)json_encode([
            'status' => 'success',
            'message' => __('Message sent successfully.'),
            'data' => $result,
        ]));
    }
}
