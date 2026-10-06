<?php
declare(strict_types=1);

namespace App\Controller\Pages;

use App\Service\HostIntent;

/**
 * PagesAuthTrait — Login and signup flows.
 */
trait PagesAuthTrait
{
    public function login()
    {
        $session = $this->getRequest()->getSession();
        // Display role: which audience this sign-in is focused on (display only —
        // landing always follows the verified backend role)
        $loginRole = strtolower(trim((string)$this->getRequest()->getQuery('role', 'customer')));
        if (!in_array($loginRole, ['customer', 'owner', 'admin'], true)) {
            $loginRole = 'customer';
        }

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $ip = $this->getRequest()->clientIp() ?? 'unknown';

            // 1. Handle AJAX Session Synchronization (from login.php fetch) — verified, whitelist only
            if (!empty($data['action']) && $data['action'] === 'login_sync' && !empty($data['user'])) {
                // Must be same-origin XHR + within attempt budget
                $isXhr = strtolower((string)$this->getRequest()->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
                if (!$isXhr) {
                    return $this->response->withStatus(400)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Bad request']));
                }
                if ($this->loginRateLimited($ip)) {
                    return $this->response->withStatus(429)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Too many attempts. Try again in a few minutes.']));
                }
                $token = (string)($data['token'] ?? ($data['access_token'] ?? ''));
                if (strlen($token) < 10 || strlen($token) > 2048) {
                    return $this->response->withStatus(401)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Missing token']));
                }
                // Verify token server-side via backend (source of truth).
                // ONLY /me verifies: it is auth-guarded (401 on bad token).
                // /user/personal-details is public and returns a demo profile
                // for missing/invalid tokens — it must NEVER authenticate.
                $verifiedUser = null;
                try {
                    $me = $this->apiClient->get('/me', [], ['Authorization' => 'Bearer ' . $token]);
                    if (is_array($me) && empty($me['_status'])) {
                        $cand = $me['user'] ?? $me['data'] ?? $me;
                        if (is_array($cand) && !empty($cand['email']) && !empty($cand['id'])) {
                            $verifiedUser = $cand;
                        }
                    }
                } catch (\Throwable $e) {
                    $verifiedUser = null;
                }
                if (empty($verifiedUser) || empty($verifiedUser['email'])) {
                    return $this->response->withStatus(401)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Token verification failed']));
                }
                // Whitelist only safe fields from verified user (never trust client-supplied arbitrary keys)
                // role is required for admin/owner portal guards + header nav
                $allow = ['id','name','first_name','last_name','full_name','email','phone','phone_number','city','country','avatar','avatar_bg','avatar_color','email_verified','role','status'];
                $user = [];
                foreach ($allow as $k) {
                    if (array_key_exists($k, $verifiedUser)) $user[$k] = $verifiedUser[$k];
                }
                // Normalise role + phone aliases
                if (!empty($user['role'])) $user['role'] = strtolower((string)$user['role']);
                if ($loginRole === 'owner' && ($user['role'] ?? '') !== 'admin') {
                    $user['role'] = 'owner';
                    try {
                        \Cake\Cache\Cache::write('auth_role_' . md5($token), ['exp' => time() + 3600, 'role' => 'owner'], 'default');
                    } catch (\Throwable $e) {}
                }
                if (empty($user['phone']) && !empty($user['phone_number'])) $user['phone'] = $user['phone_number'];
                if (empty($user['name']) && !empty($user['full_name'])) $user['name'] = $user['full_name'];
                $user['token'] = $token;
                if (empty($user['first_name']) && !empty($user['name'])) {
                    $user['first_name'] = explode(' ', trim($user['name']))[0];
                }
                $this->authService->syncSession($session, $user);

                // Persistent login cookie — auth survives session loss
                // (multi-instance file sessions, browser restarts).
                $loginResp = $this->response->withType('application/json')->withStringBody((string)json_encode([
                    'success' => true,
                    'user' => $user
                ]));
                return $this->authService->persistToken($loginResp, $token);
            }

            // 2. Handle Traditional Form Login
            $email = trim((string)($data['email'] ?? ''));
            $password = (string)($data['password'] ?? '');

            if ($email !== '' || $password !== '') {
                if ($this->loginRateLimited($ip)) {
                    $this->Flash->error(__('Too many login attempts. Please try again in a few minutes.'));
                    $this->set(compact('loginRole'));
                    return $this->render('/Pages/login');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 1) {
                    $this->Flash->error(__('Invalid email or password.'));
                    $this->set(compact('loginRole'));
                    return $this->render('/Pages/login');
                }
                $res = $this->authService->login($email, $password);
                $authToken = $res['access_token'] ?? ($res['token'] ?? null);
                $userData = $res['user'] ?? null;

                if (!empty($userData) || !empty($authToken)) {
                    $user = is_array($userData) ? $userData : [];
                    if ($authToken) {
                        $user['token'] = $authToken;
                    }
                    if (!empty($user['role'])) $user['role'] = strtolower((string)$user['role']);
                    if ($loginRole === 'owner' && ($user['role'] ?? '') !== 'admin') {
                        $user['role'] = 'owner';
                        if ($authToken) {
                            try {
                                $this->apiClient->post('/user/personal-details', ['role' => 'owner'], ['Authorization' => 'Bearer ' . $authToken]);
                            } catch (\Throwable $e) {}
                            try {
                                \Cake\Cache\Cache::write('auth_role_' . md5((string)$authToken), ['exp' => time() + 3600, 'role' => 'owner'], 'default');
                            } catch (\Throwable $e) {}
                        }
                    }
                    if (empty($user['phone']) && !empty($user['phone_number'])) $user['phone'] = $user['phone_number'];
                    if (empty($user['first_name']) && !empty($user['name'])) {
                        $user['first_name'] = explode(' ', trim($user['name']))[0];
                    }
                    $this->authService->syncSession($session, $user);
                    $this->Flash->success(__('Login successful. Welcome back!'));
                    // Persistent login cookie — portal auth no longer depends
                    // on the PHP session file surviving.
                    $persist = function (\Cake\Http\Response $resp) use ($authToken): \Cake\Http\Response {
                        return $this->authService->persistToken($resp, (string)$authToken);
                    };
                    $redirect = $this->safeRedirect(trim((string)$this->getRequest()->getQuery('redirect', '')));
                    if ($redirect !== '') {
                        return $persist($this->redirect($redirect));
                    }
                    $role = strtolower((string)($user['role'] ?? ''));
                    if ($role === 'admin') return $persist($this->redirect('/admin/dashboard'));
                    if ($role === 'owner' || $loginRole === 'owner') return $persist($this->redirect('/host/dashboard'));
                    return $persist($this->redirect('/'));
                }
                $this->Flash->error(__('Invalid email or password.'));
            }
        }

        $this->set(compact('loginRole'));
        return $this->render('/Pages/login');
    }

    public function signup()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // Resolve whether this is a host or guest signup, carrying intent in
        // from an explicit ?role=, a remembered choice, or the referring page.
        $requestedRole = HostIntent::resolve($request, $session->read('signup_intent_role'));
        $session->write('signup_intent_role', $requestedRole);

        if ($request->is('post')) {
            // The browser posts the real form to the backend via
            // /api/register. This fallback only runs when that client-side
            // path is bypassed. It used to flash "Account created
            // successfully!" and redirect to /login without creating anything,
            // so a failed registration looked like a success.
            $this->Flash->error(__(
                'Please complete registration in the form above. '
                . 'If it keeps failing, try again in a moment.'
            ));
            return $this->redirect($requestedRole === HostIntent::ROLE_OWNER
                ? '/signup?role=owner'
                : '/signup');
        }

        $this->set(compact('requestedRole'));
        return $this->render('/Pages/signup');
    }
}
