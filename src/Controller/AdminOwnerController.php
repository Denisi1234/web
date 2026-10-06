<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\PortalService;
use App\Service\RoleService;
use App\Controller\Admin\AdminFinanceTrait;
use App\Controller\Admin\AdminGuardTrait;
use App\Controller\Admin\AdminOpsTrait;
use App\Controller\Admin\AdminOverviewTrait;
use App\Controller\Admin\AdminVerifyTrait;
use App\Controller\Admin\AdminSupportTrait;
use Cake\Event\EventInterface;

/**
 * AdminOwnerController — Admin portal (working things only).
 * Mirrors admin_owner_portal: index.php dashboard, ecom-customers.php verification,
 * chart-chartist/flot/chartjs finance, support-tickets.php, reviews.php etc.
 * Headless proxy to fastnet_backend via FastnetApiClient.
 */
class AdminOwnerController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected AuthService $authService;
    protected RoleService $roleService;
    protected PortalService $portal;

    /** Per-request profile memo — sidebar/topbar read it several times per page. */
    private static array $profileMemo = [];

    use AdminFinanceTrait;
    use AdminGuardTrait;
    use AdminOpsTrait;
    use AdminOverviewTrait;
    use AdminVerifyTrait;
    use AdminSupportTrait;

    /**
     * True when any backend read on this page hit transport-null/5xx.
     * Views render an outage banner instead of false-empty lists ("No lodges
     * yet") and dead Approve/Reject buttons with no explanation.
     */
    private bool $backendDown = false;

    private function markDown(?array ...$ress): void
    {
        foreach ($ress as $res) {
            if ($res === null || (!empty($res['_status']) && (int)$res['_status'] >= 500)) {
                $this->backendDown = true;
                return;
            }
        }
    }

    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $this->set('backendDown', $this->backendDown);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
        $this->roleService = new RoleService($this->apiClient, $this->authService);
        $this->portal = new PortalService($this->apiClient);
        $this->viewBuilder()->setLayout('portal');
    }

    private function rawToken(): string
    {
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        // Session should hold raw token, but tolerate "Bearer xxx" if ever stored prefixed
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        // Session-independent fallback (see HostController::rawToken).
        if ($token === '') {
            $token = $this->authService->readToken($this->getRequest());
        }
        return $token;
    }

    private function hostHeaders(): array
    {
        $token = $this->rawToken();
        return $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
    }

    /**
     * Release the PHP session lock before slow backend I/O so parallel
     * portal requests (PJAX hover-prefetch + navigation) don't serialize on
     * the session file. Safe: later reads/writes transparently reopen it,
     * and auth state is already resolved by the caller. Mirrors HostController.
     */
    private function releaseSession(): void
    {
        try {
            $this->getRequest()->getSession()->close();
        } catch (\Throwable $e) {
        }
    }

    /**
     * Cache-bust bridge for direct-API saves (see HostController::cacheBust).
     * Fast, no backend call — invalidates matching portal scopes, then
     * redirects to a validated local path.
     */
    public function cacheBust()
    {
        $q = $this->getRequest()->getQueryParams();
        $scope = array_values(array_filter(array_map('trim', explode(',', (string)($q['scope'] ?? '')))));
        if ($scope !== []) {
            $this->portal->clear($scope);
        }
        // quiet=1: background bust after an optimistic save — no redirect.
        if (!empty($q['quiet'])) {
            $this->autoRender = false;
            return $this->response->withStatus(204);
        }
        $go = (string)($q['go'] ?? '/admin/dashboard');
        if (!str_starts_with($go, '/') || str_starts_with($go, '//')) {
            $go = '/admin/dashboard';
        }
        return $this->redirect($go);
    }


    /**
     * Backend said 401: session token is dead. Bounce to login with a safe
     * return address instead of dead-ending on "Unauthenticated".
     */
    private function bounceOnUnauth(?array $res, string $returnUrl): ?\Cake\Http\Response
    {
        if (is_array($res) && (int)($res['_status'] ?? 0) === 401) {
            $this->Flash->error(__('Session expired — please sign in again.'));
            $safe = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//') ? $returnUrl : '/admin/dashboard';
            return $this->redirect('/login?redirect=' . urlencode($safe));
        }
        return null;
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // All admin actions require admin role except maybe dashboard alias handled per-action
    }
}
