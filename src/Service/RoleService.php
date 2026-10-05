<?php
declare(strict_types=1);

namespace App\Service;

/**
 * RoleService — resolves user role from backend.
 * Mirrors admin_owner_portal/config/dz.php guard + index.php role-filter.
 */
class RoleService
{
    protected FastnetApiClient $apiClient;
    protected AuthService $authService;

    public function __construct(?FastnetApiClient $apiClient = null, ?AuthService $authService = null)
    {
        $this->apiClient = $apiClient ?: new FastnetApiClient();
        $this->authService = $authService ?: new AuthService($this->apiClient);
    }

    /** Per-request memo: verifyRole() + getRole() share one /me per token. */
    private static array $memo = [];

    /**
     * Strict backend verification for portal guards: admin | owner | customer | guest.
     *
     * Unlike getRole() this never guesses — it returns null when the backend
     * cannot be reached so callers can fall back to the session role for
     * availability (the backend still enforces on every data call).
     * Results are memoized per-request (0ms repeats) and cached 300s per
     * token in shared cache. Deduplicates with AuthService::restoreSession():
     * when the restore cache already holds the verified user, the role is
     * derived with zero backend I/O. Fail-fast 2s timeout so a slow backend
     * never blocks portal rendering — callers treat null as "keep session".
     *
     * @return string|null verified role, or null when indeterminable
     */
    public function verifyRole(?string $token): ?string
    {
        $raw = trim((string)$token);
        if (stripos($raw, 'Bearer ') === 0) {
            $raw = trim(substr($raw, 7));
        }
        if ($raw === '') return null;
        $mkey = md5($raw);
        if (isset(self::$memo[$mkey])) {
            return self::$memo[$mkey];
        }
        $cacheKey = 'auth_role_' . $mkey;
        try {
            $hit = \Cake\Cache\Cache::read($cacheKey, 'default');
            if (is_array($hit) && isset($hit['exp'], $hit['role']) && $hit['exp'] > time()) {
                self::$memo[$mkey] = $hit['role'];
                return $hit['role'];
            }
        } catch (\Throwable $e) {
        }
        // Dedupe: restoreSession() already verified this token via /me.
        try {
            $restored = \Cake\Cache\Cache::read('auth_restore_' . $mkey, 'default');
            if (is_array($restored) && isset($restored['exp'], $restored['user']) && $restored['exp'] > time()) {
                $r = strtolower((string)($restored['user']['role'] ?? ''));
                if (in_array($r, ['admin', 'owner', 'customer'], true)) {
                    $this->rememberRole($cacheKey, $r);
                    self::$memo[$mkey] = $r;
                    return $r;
                }
            }
        } catch (\Throwable $e) {
        }
        try {
            $res = $this->apiClient->get('/me', [], ['Authorization' => 'Bearer ' . $raw], 2);
        } catch (\Throwable $e) {
            return null;
        }
        if (!is_array($res)) return null;
        $status = (int)($res['_status'] ?? 0);
        if ($status === 401 || $status === 403) {
            $this->rememberRole($cacheKey, 'guest');
            return 'guest';
        }
        if ($status >= 400 || !empty($res['_error'])) return null;
        $role = $res['role'] ?? ($res['user']['role'] ?? ($res['data']['role'] ?? null));
        if (!is_string($role) || $role === '') return null;
        $role = strtolower($role);
        if (!in_array($role, ['admin', 'owner', 'customer'], true)) return null;
        $this->rememberRole($cacheKey, $role);
        return $role;
    }

    private function rememberRole(string $cacheKey, string $role): void
    {
        try {
            \Cake\Cache\Cache::write($cacheKey, ['exp' => time() + 300, 'role' => $role], 'default');
        } catch (\Throwable $e) {
        }
    }

    /**
     * Get role for token: admin | owner | customer
     * Uses /me as source of truth (requires auth), fallback to personal-details.
     */
    public function getRole(?string $token): string
    {
        if (empty($token)) return 'guest';
        $raw = trim((string)$token);
        if (stripos($raw, 'Bearer ') === 0) {
            $raw = trim(substr($raw, 7));
        }
        if ($raw === '') return 'guest';
        $headers = ['Authorization' => 'Bearer ' . $raw];
        // Primary: /me (authoritative, 401 when token invalid)
        $res2 = $this->apiClient->get('/me', [], $headers);
        if (is_array($res2) && empty($res2['_status'])) {
            if (!empty($res2['role'])) return strtolower((string)$res2['role']);
            if (!empty($res2['user']['role'])) return strtolower((string)$res2['user']['role']);
            if (!empty($res2['data']['role'])) return strtolower((string)$res2['data']['role']);
        }
        // Fallback: personal-details (public endpoint returns guest role when unauthenticated)
        $res = $this->apiClient->get('/user/personal-details', [], $headers);
        if (is_array($res) && !empty($res['details']['role']) && ($res['details']['id'] ?? null)) {
            return strtolower((string)$res['details']['role']);
        }
        if (!empty($res['role']) && !empty($res['id'])) return strtolower((string)$res['role']);
        if (!empty($res['user']['role'])) return strtolower((string)$res['user']['role']);
        // If /me said 401/403, token is invalid
        if (!empty($res2['_status']) && (int)$res2['_status'] >= 400) {
            return 'guest';
        }
        return 'customer';
    }

    public function isAdmin(?string $token): bool
    {
        return $this->getRole($token) === 'admin';
    }

    public function isOwner(?string $token): bool
    {
        return $this->getRole($token) === 'owner';
    }
}
