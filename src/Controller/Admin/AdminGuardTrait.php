<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Cache\Cache;

/**
 * AdminGuardTrait — Session-first auth, profile, and write helpers for the admin portal.
 */
trait AdminGuardTrait
{

    /**
     * Instant profile for sidebar/topbar — 0ms session-first.
     *
     * The old version blocked every admin page on
     * GET /user/personal-details (~2.3s) even though the sidebar only needs
     * a name + role for display. Now the session user (written at login and
     * by restoreSession) is returned with zero backend I/O; the backend is
     * consulted only when the session is empty, with a fail-fast 2s timeout
     * and a last-good shared-cache fallback. Backend stays source of truth
     * on refresh; display never blocks rendering.
     */
    private function cachedProfile(): array
    {
        // 0ms fast path: session already carries id/name/role.
        try {
            $sessUser = $this->getRequest()->getSession()->read('User');
            if (is_array($sessUser) && (!empty($sessUser['id']) || !empty($sessUser['email']))) {
                return $sessUser;
            }
        } catch (\Throwable $e) {
        }
        $token = $this->rawToken();
        if ($token === '') {
            return is_array($sessUser ?? null) ? $sessUser : [];
        }
        $key = 'portal_profile_' . md5($token);
        if (isset(self::$profileMemo[$key])) {
            return self::$profileMemo[$key];
        }
        try {
            $hit = Cache::read($key, 'default');
            if (is_array($hit) && !empty($hit['data'])) {
                $exp = (int)($hit['exp'] ?? 0);
                if ($exp > time()) {
                    self::$profileMemo[$key] = $hit['data'];
                    return $hit['data'];
                }
                // Stale serves instantly; refresh behind the response.
                self::$profileMemo[$key] = $hit['data'];
                $this->refreshProfileInBackground($token, $key);
                return $hit['data'];
            }
        } catch (\Throwable $e) {
        }
        $fresh = $this->authService->getPersonalDetails($token);
        if (!empty($fresh) && !empty($fresh['id'])) { // id required: public personal-details returns a demo profile (null id) for bad tokens
            $entry = ['exp' => time() + 300, 'data' => $fresh];
            self::$profileMemo[$key] = $fresh;
            try {
                Cache::write($key, $entry, 'default');
            } catch (\Throwable $e) {
            }
            return $fresh;
        }
        // Backend failed: serve last-good entry (even expired) over a blank profile.
        try {
            $stale = Cache::read($key, 'default');
            if (is_array($stale) && !empty($stale['data'])) {
                return $stale['data'];
            }
        } catch (\Throwable $e) {
        }
        return is_array($fresh) ? $fresh : [];
    }

    private function refreshProfileInBackground(string $token, string $key): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        try {
            register_shutdown_function(function () use ($token, $key): void {
                try {
                    if (function_exists('fastcgi_finish_request')) {
                        @fastcgi_finish_request();
                    }
                    $fresh = $this->authService->getPersonalDetails($token);
                    if (!empty($fresh) && !empty($fresh['id'])) {
                        self::$profileMemo[$key] = $fresh;
                        Cache::write($key, ['exp' => time() + 300, 'data' => $fresh], 'default');
                    }
                } catch (\Throwable $e) {
                }
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Instant admin gate — 0ms when the session already says admin.
     *
     * The old gate blocked every /admin/* page on GET /me (up to 5s) before
     * rendering anything. Now a session admin passes with zero backend I/O;
     * backend re-verification happens after the response flushes (shutdown)
     * and self-heals a demoted session on the next request. Only an empty
     * session with a token present pays one fail-fast (2s) verify; anonymous
     * visitors are bounced instantly with no backend call at all.
     */
    private function requireAdmin(): ?\Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $user = $session->read('User');
        $role = strtolower((string)(is_array($user) ? ($user['role'] ?? '') : ''));

        if ($role === 'admin') {
            $this->verifyAdminInBackground();
            return null;
        }

        // Signed-in but not admin: refuse instantly, no backend call.
        if (is_array($user) && !empty($user) && $role !== '') {
            return $this->refusePortalAccess(
                '/admin/dashboard',
                __('You do not have access to the admin portal.')
            );
        }

        // No session user: token present? One fail-fast verify, else login.
        $token = $this->rawToken();
        if ($token === '') {
            if ($session->read('is_logged_out') || empty($user)) {
                $this->Flash->error(__('Sign in to access the admin portal.'));
                return $this->redirect('/login?redirect=' . urlencode('/admin/dashboard'));
            }
            return $this->refusePortalAccess('/admin/dashboard', __('You do not have access to the admin portal.'));
        }

        try {
            $verified = $this->roleService->verifyRole($token);
        } catch (\Throwable $e) {
            $verified = null;
        }
        if (is_string($verified)) {
            if ($verified === 'guest') {
                try {
                    $session->delete('User');
                    $session->delete('auth_token');
                } catch (\Throwable $e) {
                }
                $this->Flash->error(__('Your session has expired. Please sign in again.'));
                return $this->redirect('/login?redirect=' . urlencode('/admin/dashboard'));
            }
            if ($verified === 'admin') {
                // Heal the session for next time (0ms from now on).
                try {
                    $session->write('User', array_merge(is_array($user) ? $user : [], ['role' => 'admin']));
                } catch (\Throwable $e) {
                }
                return null;
            }
            if (is_array($user) && $verified !== $role) {
                try {
                    $user['role'] = $verified;
                    $session->write('User', $user);
                } catch (\Throwable $e) {
                }
            }
            // Verified non-admin with a token but no usable session: refuse.
            if ($verified !== 'admin') {
                if (empty($user)) {
                    $this->Flash->error(__('Sign in to access the admin portal.'));
                    return $this->redirect('/login?redirect=' . urlencode('/admin/dashboard'));
                }
                return $this->refusePortalAccess('/admin/dashboard', __('You do not have access to the admin portal.'));
            }
            return null;
        }

        // Backend unreachable: honour a session admin, else bounce to login
        // only when there is truly no session (data calls enforce anyway).
        if ($role === 'admin') {
            return null;
        }
        if (empty($user)) {
            $this->Flash->error(__('Sign in to access the admin portal.'));
            return $this->redirect('/login?redirect=' . urlencode('/admin/dashboard'));
        }
        return $this->refusePortalAccess('/admin/dashboard', __('You do not have access to the admin portal.'));
    }

    /** Re-verify a session admin after the response flushes (self-heal demotion). */
    private function verifyAdminInBackground(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }
        try {
            $token = $this->rawToken();
            if ($token === '') {
                return;
            }
            register_shutdown_function(function () use ($token): void {
                try {
                    if (function_exists('fastcgi_finish_request')) {
                        @fastcgi_finish_request();
                    }
                    $verified = $this->roleService->verifyRole($token);
                    if (is_string($verified) && $verified !== 'admin') {
                        // Don't mutate the already-sent session; next
                        // requireAdmin() cold-verify will correct it. Only
                        // expire the role cache so it happens promptly.
                        try {
                            Cache::delete('auth_role_' . md5($token), 'default');
                        } catch (\Throwable $e) {
                        }
                    }
                } catch (\Throwable $e) {
                }
            });
        } catch (\Throwable $e) {
        }
    }

    /** True for PJAX/fetch saves that expect JSON instead of a redirect. */
    private function wantsJson(): bool
    {
        try {
            $req = $this->getRequest();
            if ($req->is('json')) {
                return true;
            }
            $accept = strtolower((string)$req->getHeaderLine('Accept'));
            if (str_contains($accept, 'application/json')) {
                return true;
            }
            return strtolower((string)$req->getHeaderLine('X-Requested-With')) === 'xmlhttprequest'
                && $req->is(['post', 'patch', 'put', 'delete']);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function jsonOk(array $extra = []): \Cake\Http\Response
    {
        $this->autoRender = false;
        $body = json_encode(array_merge(['ok' => true], $extra));
        return $this->response->withType('application/json')->withStringBody((string)$body);
    }

    private function jsonErr(string $message, int $status = 422): \Cake\Http\Response
    {
        $this->autoRender = false;
        $body = json_encode(['ok' => false, 'message' => $message]);
        return $this->response->withType('application/json')->withStatus($status)->withStringBody((string)$body);
    }

    /**
     * Unified write outcome: JSON for fetch/PJAX saves (instant toast path),
     * Flash + redirect for classic form posts. Keeps every admin action
     * реально working in both modes.
     */
    private function writeDone(string $okMsg, string $redirectAction, array $jsonExtra = []): \Cake\Http\Response
    {
        if ($this->wantsJson()) {
            return $this->jsonOk(array_merge(['message' => $okMsg], $jsonExtra));
        }
        $this->Flash->success(__($okMsg));
        return $this->redirect(['action' => $redirectAction]);
    }

    private function writeFail(string $msg, ?string $redirectAction = null, int $status = 422): \Cake\Http\Response
    {
        if ($this->wantsJson()) {
            return $this->jsonErr($msg, $status);
        }
        $this->Flash->error(__($msg));
        if ($redirectAction !== null) {
            return $this->redirect(['action' => $redirectAction]);
        }
        return $this->response->withStatus($status);
    }
}
