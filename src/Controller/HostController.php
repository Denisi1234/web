<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Controller\Host\HostFinanceTrait;
use App\Controller\Host\HostListingsTrait;
use App\Controller\Host\HostOnboardingTrait;
use App\Controller\Host\HostProfileTrait;
use App\Controller\Host\HostPropertyFormTrait;
use App\Controller\Host\HostRoomsTrait;
use App\Controller\Host\HostStaysTrait;

/**
 * HostController — Owner portal adapted to web + mobile.
 * Proxies to fastnet_backend same as admin_owner_portal (index.php:12) and mobile host_dashboard.
 * Tokens: r16, shadow 0 6 16, #2563EB, #C2410C, #F8FAFC.
 */
class HostController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected AuthService $authService;

    use HostFinanceTrait;
    use HostListingsTrait;
    use HostOnboardingTrait;
    use HostProfileTrait;
    use HostPropertyFormTrait;
    use HostRoomsTrait;
    use HostStaysTrait;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
        $this->viewBuilder()->setLayout('portal');
    }

    private function rawToken(): string
    {
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '') {
            $cookieToken = $this->authService->readToken($this->getRequest());
            if ($cookieToken !== '') {
                $token = $cookieToken;
                $this->getRequest()->getSession()->write('auth_token', $token);
            }
        }
        return $token;
    }

    private function hostHeaders(): array
    {
        $token = $this->rawToken();
        return $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
    }

    private function sessionUser(): array
    {
        $u = $this->getRequest()->getSession()->read('User');
        return is_array($u) ? $u : [];
    }

    private function isAdminUser(): bool
    {
        return strtolower((string)($this->sessionUser()['role'] ?? '')) === 'admin';
    }

    private function ownerId(): int
    {
        return (int)($this->sessionUser()['id'] ?? 0);
    }

    /**
     * Release the PHP session lock before slow backend I/O so parallel
     * portal requests (PJAX prefetch/warmup) don't serialize on the session
     * file. Safe: later session reads/writes transparently reopen it, and
     * nothing below writes auth state mid-request.
     */
    private function releaseSession(): void
    {
        try {
            $this->getRequest()->getSession()->close();
        } catch (\Throwable $e) {
        }
    }

    /** True when the last portal fetch hit a dead backend (null/5xx) — views show retry, not false-empty. */
    private bool $backendError = false;

    private function markBackend(?array $res): void
    {
        if ($res === null || (!empty($res['_status']) && (int)$res['_status'] >= 500)) {
            $this->backendError = true;
        }
    }

    /**
     * Clear host property and booking caches on create/edit/delete mutations.
     */
    private function clearHostPropertiesCache(): void
    {
        try {
            $session = $this->getRequest()->getSession();
            $session->delete('HostProperties');
            $session->delete('HostPropertiesTs');
            $session->delete('HostBookings');
            $session->delete('HostBookingsTs');
        } catch (\Throwable $e) {
        }
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        $request = $this->getRequest();

        // The host portal is owner/admin tooling. It previously rendered for
        // anyone (the backend 401s the data calls, so nothing leaked, but the
        // portal shell was public). Mirrors the admin gate: anonymous visitors
        // are sent to sign in, signed-in non-hosts are refused.
        $session = $this->getRequest()->getSession();
        $user = $session->read('User');

        // The persistent auth cookie is normally turned back into a session in
        // AppController::beforeRender(), which runs *after* this filter. Without
        // resolving it here, a real host whose session file was lost would be
        // bounced to the login page on every page load.
        if (empty($user) && !$session->read('is_logged_out')) {
            try {
                $authService = new AuthService();
                $cookieToken = $authService->readToken($request);
                if ($cookieToken !== '') {
                    $restored = $authService->restoreSession($session, $cookieToken);
                    if ($restored !== null) {
                        $user = $restored;
                    }
                }
            } catch (\Throwable $e) {
                // Never break the request on auth recovery.
            }
        }

        $role = strtolower((string)(is_array($user) ? ($user['role'] ?? '') : ''));

        // Backend is the source of truth for role: a session role alone is not
        // enough to enter owner tooling (it goes stale when the backend demotes
        // an account). Verified verdicts are cached 120s; when the backend is
        // unreachable we keep the session behaviour since every data call
        // below is still authorised server-side.
        $bearer = trim((string)$session->read('auth_token'));
        if (stripos($bearer, 'Bearer ') === 0) {
            $bearer = trim(substr($bearer, 7));
        }
        if ($bearer === '') {
            try {
                $bearer = (new AuthService())->readToken($request);
            } catch (\Throwable $e) {
                $bearer = '';
            }
        }
        if ($bearer !== '') {
            try {
                $verified = (new \App\Service\RoleService())->verifyRole($bearer);
            } catch (\Throwable $e) {
                $verified = null;
            }
            if (is_string($verified)) {
                if ($verified === 'guest') {
                    // Token is dead server-side: stop honouring the session role.
                    $session->delete('User');
                    $session->delete('auth_token');
                    $this->Flash->error(__('Your session has expired. Please sign in again.'));
                    $target = (string)($this->getRequest()->getRequestTarget() ?: '/host/dashboard');
                    $safe = str_starts_with($target, '/host') ? $target : '/host/dashboard';
                    $event->setResult($this->redirect('/login?redirect=' . urlencode($safe)));
                    return;
                }
                if ($verified !== $role && is_array($user)) {
                    // Self-healing: backend disagrees with the session — trust backend.
                    $user['role'] = $verified;
                    $session->write('User', $user);
                }
                $role = $verified;
            }
        }

        if (in_array($role, ['owner', 'admin'], true)) {
            return;
        }

        $isPublicHostPage = $request->getParam('action') === 'onboarding'
            || $request->getParam('action') === 'create'
            || $request->getParam('action') === 'upload';

        // The public-facing "list your property" entry points stay open so a
        // prospective host can start without an account.
        if ($isPublicHostPage && $role === '') {
            return;
        }

        if ($role === '' || $session->read('is_logged_out')) {
            $this->Flash->error(__('Sign in to access your host dashboard.'));
            $target = (string)($this->getRequest()->getRequestTarget() ?: '/host/dashboard');
            $safe = str_starts_with($target, '/host') ? $target : '/host/dashboard';
            $event->setResult($this->redirect('/login?redirect=' . urlencode($safe)));
            return;
        }

        // Signed in, but as a guest: a customer session can never enter host
        // tooling, no matter which credentials were used. Render the branded
        // refusal (HTTP 403) rather than throwing, because the exception
        // renderer maps every 4xx to the bare error400 page.
        $event->setResult($this->refusePortalAccess(
            (string)($this->getRequest()->getRequestTarget() ?: '/host/dashboard'),
            __('Only property hosts can access this area.')
        ));
    }

    /**
     * Backend said 401: session token is dead. Bounce to login with a safe
     * return address instead of dead-ending on "Unauthenticated".
     * Returns a redirect response, or null to continue normally.
     */
    private function bounceOnUnauth(?array $res, string $returnUrl): ?\Cake\Http\Response
    {
        if (is_array($res) && (int)($res['_status'] ?? 0) === 401) {
            $this->Flash->error(__('Session expired — please sign in again.'));
            $safe = str_starts_with($returnUrl, '/') && !str_starts_with($returnUrl, '//') ? $returnUrl : '/host/dashboard';
            return $this->redirect('/login?redirect=' . urlencode($safe));
        }
        return null;
    }
}
