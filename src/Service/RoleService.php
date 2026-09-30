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
