<?php
declare(strict_types=1);

namespace App\Controller\Pages;

/**
 * PagesOwnerTrait — Host onboarding entry point.
 */
trait PagesOwnerTrait
{
    public function joinUs()
    {
        $session = $this->getRequest()->getSession();
        $sessionUser = $session->read('User');
        $rawToken = trim((string)$session->read('auth_token'));
        if (stripos($rawToken, 'Bearer ') === 0) {
            $rawToken = trim(substr($rawToken, 7));
        }
        if ($rawToken === '') {
            $rawToken = $this->authService->readToken($this->getRequest());
        }
        $isLoggedIn = !$session->read('is_logged_out') && !empty($sessionUser);
        $userRole = strtolower((string)($sessionUser['role'] ?? ''));
        $headers = $rawToken !== '' ? ['Authorization' => 'Bearer ' . $rawToken] : [];

        // Separate accounts: a guest login is never upgraded in place.
        // Hosting needs its own account with its own sign-in (backend
        // POST /become-host is retired 410, and POST /user/personal-details
        // ignores role). Old "become_host" forms just send guests to
        // register a host account instead of faking ownership.
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            if (($data['action'] ?? '') === 'become_host' && $isLoggedIn) {
                if (!in_array($userRole, ['owner', 'admin'], true)) {
                    return $this->redirect('/signup?role=owner');
                }
                return $this->redirect('/host/onboarding');
            }
        }

        // For owners/admins: fetch my properties for status section (real data).
        // Owner → ?mine=1 (own only); admin → /admin/properties slice.
        // Never fall back to other hosts' lodges: empty stays empty.
        $myProperties = [];
        if ($isLoggedIn && $rawToken !== '' && in_array($userRole, ['owner', 'admin'], true)) {
            try {
                if ($userRole === 'admin') {
                    $pRes = $this->apiClient->get('/admin/properties', ['per_page' => 6], $headers);
                } else {
                    $pRes = $this->apiClient->get('/properties', ['mine' => 1, 'per_page' => 6], $headers);
                }
                $all = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
                if (is_array($all)) $myProperties = array_values(array_slice($all, 0, 6));
            } catch (\Throwable $e) {
                $myProperties = [];
            }
        }

        $this->set(compact('isLoggedIn', 'userRole', 'myProperties', 'sessionUser'));
        return $this->render('/Pages/join-us');
    }
    // ── Bookings Forwarders (Backward Compatibility) ───────────────────────

    /**
     * Internal safe redirect: only relative portal paths, no protocol tricks
     * (backslashes, //host, control chars). Returns '' when unsafe.
     */
    /** Current bearer token from session or persistent cookie. */
    private function currentBearerToken(): string
    {
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '') {
            $token = trim((string)$this->authService->readToken($this->getRequest()));
            if (stripos($token, 'Bearer ') === 0) {
                $token = trim(substr($token, 7));
            }
        }

        return $token;
    }

    private function safeRedirect(string $url): string
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 500) return '';
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) return '';
        if (str_contains($url, '\\') || preg_match('/[\r\n\t<>"]/', $url)) return '';
        if (!preg_match('#^/[A-Za-z0-9/_\-.?=&%#+]*$#', $url)) return '';
        return $url;
    }

    /**
     * Brute-force guard: max 10 login attempts per IP per 5 minutes.
     */
    private function loginRateLimited(string $ip): bool
    {
        $key = 'login_rate_' . md5($ip);
        $rec = \Cake\Cache\Cache::read($key, 'default');
        $now = time();
        $count = (is_array($rec) && isset($rec['exp']) && $rec['exp'] > $now) ? (int)$rec['count'] : 0;
        if ($count >= 10) return true;
        \Cake\Cache\Cache::write($key, ['count' => $count + 1, 'exp' => $now + 300]);
        return false;
    }
}
