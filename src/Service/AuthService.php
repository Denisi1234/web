<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Http\Session;

/**
 * AuthService
 * 
 * Modular authentication & user profile service for fastnetstays.com.
 */
class AuthService
{
    protected FastnetApiClient $apiClient;

    public function __construct(?FastnetApiClient $apiClient = null)
    {
        $this->apiClient = $apiClient ?: new FastnetApiClient();
    }

    /**
     * Authenticate user against fastnet_backed API
     */
    public function login(string $email, string $password): ?array
    {
        return $this->apiClient->post('/login', [
            'email' => trim($email),
            'password' => $password,
        ]);
    }

    /**
     * Register new user
     */
    public function register(array $userData): ?array
    {
        return $this->apiClient->post('/register', $userData);
    }

    /**
     * Fetch user profile / personal details
     */
    public function getPersonalDetails(?string $token = null): array
    {
        $headers = [];
        if (!empty($token)) {
            $raw = trim((string)$token);
            // Accept both raw token and already-prefixed "Bearer xxx" (controllers historically passed prefixed value)
            if (stripos($raw, 'Bearer ') === 0) {
                $headers['Authorization'] = $raw;
            } else {
                $headers['Authorization'] = 'Bearer ' . $raw;
            }
        }

        // Fail-fast: sidebar/topbar must never wait 10s on this ~2.3s endpoint.
        // Callers (AdminOwner/Host cachedProfile) already prefer session data;
        // this is the last-resort network path only.
        $res = $this->apiClient->get('/user/personal-details', [], $headers, 2);
        if (!empty($res['details']) && is_array($res['details'])) {
            return $res['details'];
        }

        return $this->getDefaultUserProfile();
    }

    /**
     * Synchronize authenticated user state to CakePHP session
     */
    public function syncSession(Session $session, array $user): void
    {
        $session->delete('is_logged_out');
        $session->write('User', $user);
        if (!empty($user['token'])) {
            $session->write('auth_token', $user['token']);
        }
    }

    /**
     * Invalidate session on logout
     */
    public function logout(Session $session): void
    {
        $session->write('is_logged_out', true);
        $session->delete('User');
        $session->delete('auth_token');
        $session->delete('token');
    }

    /**
     * Persistent login cookie name. Auth no longer depends on the PHP
     * session file surviving (multi-instance hosts give each instance its
     * own files, which randomly logged users out). The cookie carries the
     * backend token; sessions remain only as a fast cache.
     */
    public const TOKEN_COOKIE = 'fn_token';
    private const TOKEN_TTL = 2592000; // 30 days

    /**
     * Attach the persistent login cookie to a response.
     */
    public function persistToken(Response $response, string $token): Response
    {
        $token = trim($token);
        if ($token === '') {
            return $response;
        }
        $secure = str_starts_with((string)Configure::read('App.fullBaseUrl', ''), 'https://');
        $cookie = Cookie::create(static::TOKEN_COOKIE, $token, [
            'expires' => time() + static::TOKEN_TTL,
            'path' => '/',
            'httponly' => true,
            'secure' => $secure,
            'samesite' => 'Lax',
        ]);
        return $response->withCookie($cookie);
    }

    /**
     * Expire the persistent login cookie.
     */
    public function clearToken(Response $response): Response
    {
        $cookie = Cookie::create(static::TOKEN_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        return $response->withCookie($cookie);
    }

    /**
     * Read the persistent token (raw, no Bearer prefix) or '' when absent.
     */
    public function readToken(ServerRequest $request): string
    {
        $cookies = $request->getCookieParams();
        $token = trim((string)($cookies[static::TOKEN_COOKIE] ?? ''));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        return (strlen($token) >= 10 && strlen($token) <= 4096) ? $token : '';
    }

    /**
     * Rebuild session auth from the persistent cookie token (transparent
     * re-login after session loss). Returns the restored user or null.
     * Verified users are memoized per-request + cached 300s by token hash —
     * never hits backend twice for the same token. Fail-fast 2s so a slow
     * backend never blocks rendering. Never throws.
     */
    private static array $restoreMemo = [];

    public function restoreSession(Session $session, string $token): ?array
    {
        $token = trim($token);
        if ($token === '' || $session->read('is_logged_out')) {
            return null;
        }
        $mkey = md5($token);
        if (isset(self::$restoreMemo[$mkey])) {
            $user = self::$restoreMemo[$mkey];
            try {
                $session->write('User', $user);
                $session->write('auth_token', $token);
            } catch (\Throwable $e) {
            }
            return $user;
        }
        $cacheKey = 'auth_restore_' . $mkey;
        try {
            $cached = \Cake\Cache\Cache::read($cacheKey, 'default');
            if (is_array($cached) && isset($cached['exp'], $cached['user']) && $cached['exp'] > time()) {
                $user = $cached['user'];
                self::$restoreMemo[$mkey] = $user;
                $session->write('User', $user);
                $session->write('auth_token', $token);
                return $user;
            }
        } catch (\Throwable $e) {
        }
        try {
            $me = $this->apiClient->get('/me', [], ['Authorization' => 'Bearer ' . $token], 2);
            $cand = null;
            if (is_array($me) && empty($me['_status'])) {
                $cand = $me['user'] ?? $me['data'] ?? $me;
            }
            if (!is_array($cand) || (empty($cand['email']) && empty($cand['id']))) {
                // No secrets logged — key names + status only.
                $keys = is_array($me) ? implode(',', array_keys($me)) : gettype($me);
                $ckeys = is_array($cand) ? implode(',', array_keys($cand)) : gettype($cand);
                \Cake\Log\Log::debug(sprintf('[auth] restore rejected: me_keys=[%s] cand_keys=[%s] status=%s', $keys, $ckeys, $me['_status'] ?? 'ok'));
                return null;
            }
            $allow = ['id','name','first_name','last_name','full_name','email','phone','phone_number','city','country','avatar','avatar_bg','avatar_color','email_verified','role','status'];
            $user = [];
            foreach ($allow as $k) {
                if (array_key_exists($k, $cand)) $user[$k] = $cand[$k];
            }
            if (!empty($user['role'])) $user['role'] = strtolower((string)$user['role']);
            if (empty($user['phone']) && !empty($user['phone_number'])) $user['phone'] = $user['phone_number'];
            if (empty($user['name']) && !empty($user['full_name'])) $user['name'] = $user['full_name'];
            if (empty($user['first_name']) && !empty($user['name'])) {
                $user['first_name'] = explode(' ', trim($user['name']))[0];
            }
            $user['token'] = $token;
            self::$restoreMemo[$mkey] = $user;
            $session->write('User', $user);
            $session->write('auth_token', $token);
            try {
                \Cake\Cache\Cache::write($cacheKey, ['exp' => time() + 300, 'user' => $user], 'default');
            } catch (\Throwable $e) {
            }
            return $user;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if active session is authenticated
     */
    public function isAuthenticated(Session $session): bool
    {
        if ($session->read('is_logged_out')) {
            return false;
        }
        return $session->check('User');
    }

    /**
     * Blank profile structure for unauthenticated or new user
     */
    public function getDefaultUserProfile(): array
    {
        return [
            'id' => null,
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'city' => '',
            'country' => 'Tanzania',
            'avatar' => null,
            'email_verified' => false
        ];
    }
}
