<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Controller\Account\AccountBookingsTrait;
use App\Controller\Account\AccountPrefsTrait;
use App\Controller\Account\AccountProfileTrait;

/**
 * AccountController
 * Modular user dashboard, profile settings, bookings history, and preferences controller.
 */
class AccountController extends AppController
{
    protected AuthService $authService;

    use AccountBookingsTrait;
    use AccountPrefsTrait;
    use AccountProfileTrait;
    protected FastnetApiClient $apiClient;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
    }

    /**
     * Session-independent auth token (persistent cookie survives session loss).
     */
    private function portalToken(): string
    {
        $session = $this->getRequest()->getSession();
        $raw = $session->read('auth_token');
        $token = trim((string)$raw);
        if ($token === '') {
            $token = $this->authService->readToken($this->getRequest());
        }
        return $token;
    }
}
