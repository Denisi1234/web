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
     * Tries /user/personal-details first (already whitelisted), fallback /me.
     */
    public function getRole(?string $token): string
    {
        if (empty($token)) return 'guest';
        $headers = ['Authorization' => 'Bearer ' . $token];
        // Try personal-details (web already uses this)
        $res = $this->apiClient->get('/user/personal-details', [], $headers);
        if (!empty($res['details']['role'])) return strtolower((string)$res['details']['role']);
        if (!empty($res['role'])) return strtolower((string)$res['role']);
        if (!empty($res['user']['role'])) return strtolower((string)$res['user']['role']);
        // Fallback /me (admin_owner_portal uses /me)
        $res2 = $this->apiClient->get('/me', [], $headers);
        if (!empty($res2['role'])) return strtolower((string)$res2['role']);
        if (!empty($res2['user']['role'])) return strtolower((string)$res2['user']['role']);
        if (!empty($res2['data']['role'])) return strtolower((string)$res2['data']['role']);
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
