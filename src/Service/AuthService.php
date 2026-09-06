<?php
declare(strict_types=1);

namespace App\Service;

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
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $res = $this->apiClient->get('/user/personal-details', [], $headers);
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
