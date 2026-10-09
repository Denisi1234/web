<?php
declare(strict_types=1);

namespace App\Controller\Account;

/**
 * AccountProfileTrait — Menu, profile, security, and bookings list.
 */
trait AccountProfileTrait
{
    public function menu()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/menu');
    }

    public function myProfile()
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        // Real profile: merge default + session (extra fields) + backend (authoritative) — no fake Deni
        $sessionProfile = $session->read('User') ?? [];
        $backendProfile = null;
        if ($token !== '') {
            $res = $this->apiClient->get('/user/personal-details', [], $headers);
            if (!empty($res['details']) && is_array($res['details'])) {
                $backendProfile = $res['details'];
            } elseif (!empty($res['data']) && is_array($res['data'])) {
                $backendProfile = $res['data'];
            }
        }
        $default = $this->authService->getPersonalDetails($token);
        // session keeps date_of_birth/gender/address etc. that backend doesn't yet store
        $userProfile = array_merge($default, $sessionProfile, $backendProfile ?? []);
        // ensure sessionProfile extra fields not lost if backend only has 6 fields
        foreach (['date_of_birth','gender','address','emergency_contact','bio','avatar'] as $k) {
            if (empty($userProfile[$k]) && !empty($sessionProfile[$k])) $userProfile[$k] = $sessionProfile[$k];
        }

        if ($this->getRequest()->is(['post', 'put'])) {
            $isJson = $this->getRequest()->is('json') || $this->getRequest()->getHeaderLine('Content-Type') === 'application/json';
            $data = $isJson ? (array)json_decode((string)$this->getRequest()->getBody(), true) : (array)$this->getRequest()->getData();
            if (empty($data)) $data = (array)$this->getRequest()->getData();

            // Whitelist real fields only — all 8 sections
            $allow = ['first_name','last_name','full_name','name','email','phone','phone_number','date_of_birth','gender','address','emergency_contact','bio','avatar','avatar_bg','avatar_color'];
            $payload = [];
            foreach ($allow as $k) {
                if (array_key_exists($k, $data) && $data[$k] !== null) $payload[$k] = trim((string)$data[$k]);
            }
            if (isset($payload['name']) && !isset($payload['full_name'])) $payload['full_name'] = $payload['name'];
            if (isset($payload['phone']) && !isset($payload['phone_number'])) $payload['phone_number'] = $payload['phone'];

            $apiRes = null;
            if ($token !== '') {
                $apiRes = $this->apiClient->post('/user/personal-details', $payload, $headers);
            }
            $updatedDetails = $apiRes['details'] ?? $apiRes['data'] ?? null;
            if (is_array($updatedDetails) && !empty($updatedDetails)) {
                $userProfile = array_merge($userProfile, $updatedDetails);
            } else {
                $userProfile = array_merge($userProfile, $payload);
            }
            // Sync session with real backend response
            $session->write('User', $userProfile);
            if ($isJson) {
                $this->set(compact('userProfile'));
                return $this->response->withType('application/json')->withStringBody(json_encode(['status'=>'success','details'=>$userProfile]));
            }
            $this->Flash->success(__('Your personal details have been updated.'));
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/my-profile');
    }

    public function accountSecurity()
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $userProfile = $this->authService->getPersonalDetails($token);

        if ($this->getRequest()->is(['post', 'put'])) {
            $isJson = $this->getRequest()->is('json') || str_contains($this->getRequest()->getHeaderLine('Content-Type'), 'json');
            $data = $isJson ? (array)json_decode((string)$this->getRequest()->getBody(), true) : (array)$this->getRequest()->getData();
            if (empty($data)) $data = (array)$this->getRequest()->getData();

            // Delete account
            if (($data['action'] ?? '') === 'delete') {
                if ($token !== '') {
                    $this->apiClient->delete('/user', ['Authorization' => 'Bearer ' . $token]);
                }
                $this->authService->logout($session);
                try { $session->destroy(); } catch (\Throwable $e) {}
                $clearCookie = function (\Cake\Http\Response $resp): \Cake\Http\Response {
                    return $this->authService->clearToken($resp);
                };
                if ($isJson) {
                    return $clearCookie($this->response->withType('application/json')->withStringBody(json_encode(['status'=>'success','message'=>'Account deleted'])));
                }
                $this->Flash->success(__('Your account has been deleted.'));
                return $clearCookie($this->redirect('/'));
            }

            $currentPassword = trim((string)($data['current_password'] ?? ''));
            $newPassword = trim((string)($data['new_password'] ?? ''));
            $confirmPassword = trim((string)($data['confirm_password'] ?? ''));

            if ($newPassword === '' || $confirmPassword === '') {
                $msg = __('Please fill all password fields.');
                if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>$msg]));
                $this->Flash->error($msg);
            } elseif ($newPassword !== $confirmPassword) {
                $msg = __('New password and confirmation do not match.');
                if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>$msg]));
                $this->Flash->error($msg);
            } elseif (strlen($newPassword) < 8) {
                $msg = __('New password must be at least 8 characters.');
                if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>$msg]));
                $this->Flash->error($msg);
            } else {
                // Verify current password via login attempt, then update via backend if endpoint exists
                $email = $userProfile['email'] ?? $session->read('User.email') ?? '';
                $verified = false;
                if ($email !== '' && $currentPassword !== '') {
                    $chk = $this->apiClient->post('/login', ['email'=>$email,'password'=>$currentPassword]);
                    if (!empty($chk['access_token']) || !empty($chk['token']) || !empty($chk['user'])) $verified = true;
                }
                if (!$verified && $currentPassword === '') {
                    $msg = __('Current password is required.');
                    if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>$msg]));
                    $this->Flash->error($msg);
                } else {
                        $headers = $token !== '' ? ['Authorization'=>'Bearer '.$token] : [];
                    $res = $this->apiClient->post('/user/password', ['current_password'=>$currentPassword,'new_password'=>$newPassword,'new_password_confirmation'=>$confirmPassword], $headers);
                    if (!empty($res['_status']) && (int)$res['_status'] === 422) {
                        $msg = $res['message'] ?? __('Current password is incorrect.');
                        if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>$msg]));
                        $this->Flash->error($msg);
                    } else {
                        $msg = $res['message'] ?? __('Your password has been updated securely.');
                        // refresh token if backend rotated
                        if (!empty($res['access_token'])) {
                            $session->write('auth_token', $res['access_token']);
                            $u = $session->read('User');
                            if (is_array($u)) { $u['token'] = $res['access_token']; $session->write('User', $u); }
                        }
                        if ($isJson) return $this->response->withType('application/json')->withStringBody(json_encode(['status'=>'success','message'=>$msg]));
                        $this->Flash->success($msg);
                        return $this->redirect(['action'=>'accountSecurity']);
                    }
                }
            }
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/account-security');
    }

    public function myBooking()
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $userProfile = $this->authService->getPersonalDetails($token);
        $bookings = [];

        $headers = [];
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $userEmail = $userProfile['email'] ?? $session->read('User.email') ?? '';
        $queryParams = $userEmail !== '' ? ['email' => $userEmail] : [];

        if ($token !== '' || $userEmail !== '') {
            $res = $this->apiClient->get('/bookings', $queryParams, $headers);
            if (!empty($res['data']) && is_array($res['data'])) {
                $bookings = $res['data'];
            } elseif (is_array($res) && isset($res[0])) {
                $bookings = $res;
            }
        }

        // Also check any session-persisted bookings (paid on this device —
        // written at the moment payment confirms, so the dashboard is real
        // even when the backend list lags or the guest has no token).
        $sessionBookings = $session->read('user_bookings') ?? [];
        if (is_array($sessionBookings) && !empty($sessionBookings)) {
            $existingKeys = [];
            foreach ($bookings as $eb) {
                if (!is_array($eb)) continue;
                foreach (['booking_code', 'reference', 'id', 'booking_id'] as $k) {
                    if (!empty($eb[$k])) $existingKeys[(string)$eb[$k]] = true;
                }
            }
            foreach ($sessionBookings as $sb) {
                if (!is_array($sb)) continue;
                $dup = false;
                foreach (['booking_code', 'reference', 'id', 'booking_id'] as $k) {
                    if (!empty($sb[$k]) && isset($existingKeys[(string)$sb[$k]])) { $dup = true; break; }
                }
                if (!$dup) {
                    $bookings[] = $sb;
                    foreach (['booking_code', 'reference', 'id', 'booking_id'] as $k) {
                        if (!empty($sb[$k])) $existingKeys[(string)$sb[$k]] = true;
                    }
                }
            }
        }

        // In-flight mobile-money payments on this device (younger than 2h):
        // offer "Complete payment" so a closed payment page is resumable.
        $pendingPayments = [];
        $pendingIndex = $session->read('user_pending_payments') ?? [];
        if (is_array($pendingIndex) && !empty($pendingIndex)) {
            $kept = [];
            foreach ($pendingIndex as $entry) {
                if (!is_array($entry) || empty($entry['payment_id'])) continue;
                $age = time() - (int)($entry['created_at'] ?? 0);
                $live = $session->read('pending_payments.' . $entry['payment_id']);
                if ($age > 7200 || !is_array($live) || empty($live['booking_id'])) continue;
                $kept[] = $entry;
                $pendingPayments[] = [
                    'payment_id' => (string)$entry['payment_id'],
                    'booking_code' => (string)($live['booking_code'] ?? $entry['booking_code'] ?? ''),
                    'amount' => (float)($live['amount'] ?? 0),
                    'payment_method' => (string)($live['payment_method'] ?? ''),
                    'created_at' => (int)($live['created_at'] ?? $entry['created_at'] ?? time()),
                ];
            }
            $session->write('user_pending_payments', $kept);
        }

        $userBookings = $bookings;
        $this->set(compact('userProfile', 'bookings', 'userBookings', 'pendingPayments'));
        return $this->render('/Pages/my-booking');
    }
}
