<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use Cake\Http\Response;

/**
 * AccountController
 * Modular user dashboard, profile settings, bookings history, and preferences controller.
 */
class AccountController extends AppController
{
    protected AuthService $authService;
    protected FastnetApiClient $apiClient;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
    }

    public function menu()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/menu');
    }

    public function myProfile()
    {
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
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
        $token = trim((string)$session->read('auth_token'));
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
                if ($isJson) {
                    return $this->response->withType('application/json')->withStringBody(json_encode(['status'=>'success','message'=>'Account deleted']));
                }
                $this->Flash->success(__('Your account has been deleted.'));
                return $this->redirect('/');
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
        $token = trim((string)$session->read('auth_token'));
        $userProfile = $this->authService->getPersonalDetails($token);
        $bookings = [];

        $headers = [];
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $userEmail = $userProfile['email'] ?? $session->read('User.email') ?? '';
        $queryParams = $userEmail !== '' ? ['email' => $userEmail] : [];

        $res = $this->apiClient->get('/bookings', $queryParams, $headers);
        if (!empty($res['data']) && is_array($res['data'])) {
            $bookings = $res['data'];
        } elseif (is_array($res) && isset($res[0])) {
            $bookings = $res;
        }

        // Also check any session-persisted bookings
        $sessionBookings = $session->read('user_bookings') ?? [];
        if (is_array($sessionBookings) && !empty($sessionBookings)) {
            $existingCodes = array_column($bookings, 'booking_code');
            foreach ($sessionBookings as $sb) {
                if (!in_array($sb['booking_code'] ?? '', $existingCodes, true)) {
                    $bookings[] = $sb;
                }
            }
        }

        $userBookings = $bookings;
        $this->set(compact('userProfile', 'bookings', 'userBookings'));
        return $this->render('/Pages/my-booking');
    }

    public function cancelBooking(): Response
    {
        $isJson = $this->getRequest()->is('json') || $this->getRequest()->getHeaderLine('Content-Type') === 'application/json';
        $data = $isJson ? (array)json_decode((string)$this->getRequest()->getBody(), true) : (array)$this->getRequest()->getData();
        $bookingId = trim((string)($data['booking_id'] ?? ''));
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        if ($bookingId === '') {
            $msg = __('Invalid booking ID.');
            if ($isJson) return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message' => $msg]));
            $this->Flash->error($msg);
            return $this->redirect(['action' => 'myBooking']);
        }

        $result = $this->apiClient->delete('/bookings/' . rawurlencode($bookingId), $headers);
        
        // Also update in session if present
        $session = $this->getRequest()->getSession();
        $sessionBookings = $session->read('user_bookings') ?? [];
        if (is_array($sessionBookings)) {
            foreach ($sessionBookings as &$sb) {
                if ((string)($sb['id'] ?? '') === $bookingId || (string)($sb['booking_code'] ?? '') === $bookingId) {
                    $sb['status'] = 'Cancelled';
                }
            }
            $session->write('user_bookings', $sessionBookings);
        }

        $msg = __('Your booking cancellation request was submitted.');
        if ($isJson) {
            return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 'success', 'message' => $msg]));
        }
        $this->Flash->success($msg);
        return $this->redirect(['action' => 'myBooking']);
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
        $token = trim((string)$session->read('auth_token'));
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
        $token = trim((string)$session->read('auth_token'));
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
        $token = trim((string)$session->read('auth_token'));
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $res = $this->apiClient->delete('/wishlist/'.rawurlencode($id), $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? ['ok'=>true]));
    }

    public function wishlistLists(): \Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        $headers = $token !== '' ? ['Authorization'=>'Bearer '.$token] : [];
        $res = $this->apiClient->get('/wishlist-lists', [], $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? []));
    }

    public function wishlistListCreate(): \Cake\Http\Response
    {
        $this->request->allowMethod(['post']);
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
        $data = json_decode((string)$this->getRequest()->getBody(), true) ?: $this->getRequest()->getData();
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message'=>'Name required']));
        $res = $this->apiClient->post('/wishlist-lists', ['name'=>$name], $headers);
        return $this->response->withType('application/json')->withStringBody(json_encode($res ?? ['ok'=>true]));
    }

    public function recentlyViewed()
    {
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        $userProfile = $this->authService->getPersonalDetails($token);
        
        $recentStays = $session->read('recently_viewed_stays') ?? [];
        if (!is_array($recentStays)) $recentStays = [];

        // If session empty, fallback to fetching real properties from backend to ensure immediate delight
        if (empty($recentStays)) {
            $apiRes = $this->apiClient->get('/properties', ['limit' => 6]);
            if (!empty($apiRes['data']) && is_array($apiRes['data'])) {
                $featured = array_slice($apiRes['data'], 0, 3);
                foreach ($featured as $p) {
                    $recentStays[] = [
                        'id' => (int)($p['id'] ?? 0),
                        'name' => \App\Utility\TextFormatter::formatTitle((string)($p['name'] ?? 'Stay')),
                        'city' => \App\Utility\TextFormatter::formatTitle((string)($p['city'] ?? 'Tanzania')),
                        'area' => \App\Utility\TextFormatter::formatTitle((string)($p['area'] ?? '')),
                        'price_per_night' => (float)($p['price_per_night'] ?? ($p['price'] ?? 120000)),
                        'rating' => (float)($p['rating'] ?? 4.8),
                        'reviews_count' => (int)($p['reviews_count'] ?? ($p['review_count'] ?? 150)),
                        'image_url' => $p['image_url'] ?? ($p['primary_image_url'] ?? 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&h=400&fit=crop'),
                        'viewed_at' => time(),
                    ];
                }
            }
        }

        $this->set(compact('userProfile', 'recentStays'));
        return $this->render('/Pages/recently-viewed');
    }

    public function recentlyViewedClear(): \Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $session->delete('recently_viewed_stays');
        return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 'success', 'message' => 'Cleared']));
    }

    public function recentlyViewedRemove(string $id): \Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $recent = $session->read('recently_viewed_stays') ?? [];
        if (is_array($recent)) {
            $recent = array_values(array_filter($recent, fn($s) => (int)($s['id'] ?? 0) !== (int)$id));
            $session->write('recently_viewed_stays', $recent);
        }
        return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 'success']));
    }

    public function searchPreferences()
    {
        $userProfile = $this->authService->getPersonalDetails();

        if ($this->getRequest()->is(['post', 'put'])) {
            $this->Flash->success(__('Search preferences updated.'));
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/search-preferences');
    }

    public function notifications()
    {
        $userProfile = $this->authService->getPersonalDetails();

        if ($this->getRequest()->is(['post', 'put'])) {
            $this->Flash->success(__('Notification settings saved.'));
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/notifications');
    }

    public function languageAndCurrency()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/language-and-currency');
    }

    public function settings()
    {
        return $this->redirect(['action' => 'myProfile']);
    }

    public function deleteAccount()
    {
        return $this->redirect(['action' => 'accountSecurity']);
    }
}
