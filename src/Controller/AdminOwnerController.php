<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\PortalService;
use App\Service\RoleService;
use Cake\Cache\Cache;
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

    public function dashboard()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $this->releaseSession();

        // One parallel batch instead of 4 sequential HTTP calls per cold view.
        // Slow-moving aggregates get long TTLs + stale-while-revalidate, so
        // warm views serve in ~1ms with zero backend I/O; bookings stay short.
        $batch = $this->portal->getMulti([
            'props' => ['endpoint' => '/admin/properties', 'params' => [], 'ttl' => 300],
            'books' => ['endpoint' => '/admin/bookings', 'params' => [], 'ttl' => 60],
            'users' => ['endpoint' => '/admin/users', 'params' => ['role' => 'owner'], 'ttl' => 300],
            'verif' => ['endpoint' => '/admin/verification/summary', 'params' => [], 'ttl' => 600],
        ], $headers);
        $propRes = $batch['props'];
        if ($bounce = $this->bounceOnUnauth($propRes, '/admin/dashboard')) return $bounce;
        if (empty($propRes) || !empty($propRes['_status'])) {
            $propRes = $this->portal->get('/properties', [], $headers, 120);
        }
        $properties = $propRes['data'] ?? (isset($propRes[0]) ? $propRes : []);
        if (!is_array($properties)) $properties = [];

        $bookRes = $batch['books'];
        $bookings = $bookRes['data'] ?? (isset($bookRes[0]) ? $bookRes : []);
        if (!is_array($bookings)) $bookings = [];

        $userRes = $batch['users'];
        $owners = $userRes['data'] ?? (isset($userRes[0]) ? $userRes : []);
        if (!is_array($owners)) $owners = [];

        // Verification queue counts come from one purpose-built aggregate rather
        // than being derived client-side from the full property/booking/user
        // collections above.
        $verRes = $batch['verif'];
        $verification = $verRes['data'] ?? $verRes;
        if (!is_array($verification)) $verification = [];
        $verificationCounts = is_array($verification['counts'] ?? null) ? $verification['counts'] : [];

        // Commission comes from each booking's stored commission_rate /
        // platform_fee / owner_payout. These were previously re-derived as a flat
        // revenue * 0.10 / * 0.90 here, which discarded the real per-booking
        // split and produced figures that could never reconcile with the
        // backend's ledger.
        $revenue = array_sum(array_map(fn($b) => (float)($b['total_price'] ?? 0), $bookings));

        $sumFee = static function (array $rows, string $column): float {
            $total = 0.0;
            foreach ($rows as $row) {
                if (isset($row[$column]) && $row[$column] !== null) {
                    $total += (float) $row[$column];
                }
            }
            return $total;
        };

        $stats = [
            'properties' => count($properties),
            'bookings' => count($bookings),
            'owners' => count($owners),
            'revenue' => $revenue,
            'platform_fee' => $sumFee($bookings, 'platform_fee'),
            'owner_earnings' => $sumFee($bookings, 'owner_payout'),
        ];

        // Recent properties for table
        $recentProperties = array_slice($properties, 0, 8);
        $this->markDown($propRes, $bookRes, $userRes, $verRes);
        $this->set(compact(
            'userProfile', 'properties', 'recentProperties', 'bookings', 'owners',
            'stats', 'verification', 'verificationCounts'
        ));
    }

    public function owners()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $this->releaseSession();

        $q = $this->getRequest()->getQueryParams();
        $page = max(1, (int)($q['page'] ?? 1));
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $params = ['role' => 'owner', 'page' => $page, 'per_page' => 15];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        // Users + financial summary fly together (was 2 sequential calls).
        // Long TTL + stale-while-revalidate: filter pages serve in ~1ms warm.
        $batch = $this->portal->getMulti([
            'users' => ['endpoint' => '/admin/users', 'params' => $params, 'ttl' => 300],
            'fin' => ['endpoint' => '/admin/owners/financial-summary', 'params' => $params, 'ttl' => 300],
        ], $headers);
        $res = $batch['users'];
        if ($bounce = $this->bounceOnUnauth($res, '/admin/owners')) return $bounce;
        $users = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($users)) $users = [];

        $finRes = $batch['fin'];
        $financial = $finRes['data'] ?? $finRes;
        if (!is_array($financial)) $financial = [];

        $this->markDown($res, $finRes);
        $this->set(compact('userProfile', 'users', 'financial', 'search', 'status', 'page'));
    }

    public function lodges()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $this->releaseSession();

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        // NOTE: kept unpaginated (per_page 50) on purpose — the template has
        // no pager yet and the backend shape is unverified, so capping the
        // fetch could hide listings. Pagination UI is a follow-up, not this.
        $params = ['per_page' => 50];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        $res = $this->portal->get('/admin/properties', $params, $headers, 300);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/lodges')) return $bounce;
        $properties = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($properties)) $properties = [];
        if (empty($properties) && !empty($res) && empty($res['_status'])) $properties = $res;
        if (empty($properties) && $res === null) {
            // Backend dead: stale already served inside PortalService when
            // available; otherwise render the shell instantly (no hang).
            $properties = [];
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'properties', 'search', 'status'));
    }

    public function financeOverview()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $q = $this->getRequest()->getQueryParams();
        $dateRange = trim((string)($q['date_range'] ?? '30_days'));
        $params = ['date_range' => $dateRange];
        if (!empty($q['date_from'])) $params['date_from'] = $q['date_from'];
        if (!empty($q['date_to'])) $params['date_to'] = $q['date_to'];

        $this->releaseSession();
        $res = $this->portal->get('/finance/overview', $params, $headers, 300);
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->portal->get('/admin/dashboard-stats', $params, $headers, 300);
        }
        $finance = $res['data'] ?? $res;
        if (!is_array($finance)) $finance = [];

        $this->markDown($res);
        $this->set(compact('userProfile', 'finance', 'dateRange'));
    }

    public function financeLedger()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $q = $this->getRequest()->getQueryParams();
        $params = [];
        foreach (['search','transaction_id','booking_reference','min_amount','max_amount','date_from','date_to','owner_id','property_id','transaction_type','payment_status','payout_status','sort_by','page','per_page'] as $k) {
            if (isset($q[$k]) && $q[$k] !== '') $params[$k] = $q[$k];
        }
        if (empty($params['per_page'])) $params['per_page'] = 15;
        if (empty($params['page'])) $params['page'] = 1;

        $this->releaseSession();
        $res = $this->portal->get('/finance/ledger', $params, $headers, 60);
        $ledger = $res['data'] ?? $res;
        if (!is_array($ledger)) $ledger = [];
        $transactions = $ledger['data'] ?? $ledger;
        if (!is_array($transactions)) $transactions = [];

        // Keep raw for pagination meta
        $meta = $ledger['meta'] ?? null;

        $this->markDown($res);
        $this->set(compact('userProfile', 'transactions', 'ledger', 'meta', 'params'));
    }

    public function payouts()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            // Handle payout status update via same page POST
            if (!empty($data['payout_id']) && !empty($data['status'])) {
                $id = trim((string)$data['payout_id']);
                $status = strtoupper(trim((string)$data['status']));
                $allowed = ['REQUESTED', 'PROCESSING', 'PAID', 'FAILED'];
                if ($id === '' || !in_array($status, $allowed, true)) {
                    return $this->writeFail('Invalid payout update.', 'payouts', 400);
                }
                $notes = trim((string)($data['notes'] ?? ''));
                $payload = ['status' => $status];
                if ($notes !== '') {
                    $payload['notes'] = $notes;
                    $payload['admin_notes'] = $notes;
                }
                $this->releaseSession();
                $candidates = ['/payouts/' . $id . '/status', '/admin/payouts/' . $id . '/status'];
                $up = null;
                foreach ($candidates as $ep) {
                    $attempt = $this->apiClient->patch($ep, $payload, $headers);
                    if ($attempt === null) {
                        $up = null;
                        break;
                    }
                    if ((int)($attempt['_status'] ?? 200) === 404 && $ep !== end($candidates)) {
                        continue;
                    }
                    $up = $attempt;
                    break;
                }
                if ($bounce = $this->bounceOnUnauth($up, '/admin/finance/payouts')) return $bounce;
                if ($up === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'payouts', 502);
                }
                if (!empty($up['_status']) && (int)$up['_status'] >= 400) {
                    $msg = (string)($up['message'] ?? 'Could not update payout.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msg, (int)$up['_status']);
                    }
                    $this->Flash->error(__($msg));
                } else {
                    $this->portal->clear(['_payouts', 'payouts', 'finance', 'dashboard']);
                    return $this->writeDone('Payout updated.', 'payouts', ['status' => $status]);
                }
            } elseif ($data !== []) {
                return $this->writeFail('Payout and status are required.', 'payouts', 400);
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $status = trim((string)($q['status'] ?? ''));
        $params = [];
        if ($status !== '') $params['status'] = $status;

        $this->releaseSession();
        $batch = $this->portal->getMulti([
            'payouts' => ['endpoint' => '/payouts', 'params' => $params, 'ttl' => 60],
            'fin' => ['endpoint' => '/finance/overview', 'params' => [], 'ttl' => 300],
        ], $headers);
        $res = $batch['payouts'];
        if ($bounce = $this->bounceOnUnauth($res, '/admin/finance/payouts')) return $bounce;
        $payouts = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($payouts)) $payouts = [];

        // Also fetch finance overview for cards (cached 120s)
        $fin = $batch['fin'];
        $finance = $fin['data'] ?? $fin;
        if (!is_array($finance)) $finance = [];

        $this->markDown($res, $fin);
        $this->set(compact('userProfile', 'payouts', 'finance', 'status'));
    }

    public function support()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $this->releaseSession();
            $data = (array)$this->getRequest()->getData();
            $action = trim((string)($data['action'] ?? ''));
            if ($action === 'reply' && !empty($data['ticket_id'])) {
                $tid = trim((string)$data['ticket_id']);
                $msg = trim((string)($data['message'] ?? $data['body'] ?? $data['reply'] ?? ''));
                if ($tid === '' || $msg === '') {
                    return $this->writeFail('Reply message is required.', 'support', 400);
                }
                if (strlen($msg) > 5000) {
                    return $this->writeFail('Reply is too long.', 'support', 400);
                }
                // Send both keys: backends disagree on message vs body.
                $rep = $this->apiClient->post('/tickets/' . $tid . '/reply', ['message' => $msg, 'body' => $msg], $headers);
                if ($rep === null) {
                    // Alias fallback: some backends nest replies under /support.
                    $rep = $this->apiClient->post('/support/tickets/' . $tid . '/reply', ['message' => $msg], $headers);
                }
                if ($bounce = $this->bounceOnUnauth($rep, '/admin/support')) return $bounce;
                if ($rep === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'support', 502);
                }
                if (!empty($rep['_status']) && (int)$rep['_status'] >= 400) {
                    $msgErr = (string)($rep['message'] ?? 'Could not send reply.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msgErr, (int)$rep['_status']);
                    }
                    $this->Flash->error(__($msgErr));
                } else {
                    $this->portal->clear(['_tickets', 'tickets', 'threads']);
                    return $this->writeDone('Reply sent.', 'support');
                }
            } elseif ($action === 'status' && !empty($data['ticket_id'])) {
                $tid = trim((string)$data['ticket_id']);
                $st = trim((string)($data['status'] ?? ''));
                if ($tid === '' || $st === '') {
                    return $this->writeFail('Ticket and status are required.', 'support', 400);
                }
                $allowed = ['Open', 'In Progress', 'Resolved', 'Closed'];
                $norm = null;
                foreach ($allowed as $a) {
                    if (strcasecmp($a, $st) === 0) {
                        $norm = $a;
                        break;
                    }
                }
                if ($norm === null) {
                    return $this->writeFail('Invalid ticket status.', 'support', 400);
                }
                $pat = $this->apiClient->patch('/tickets/' . $tid . '/status', ['status' => $norm], $headers);
                if ($bounce = $this->bounceOnUnauth($pat, '/admin/support')) return $bounce;
                if ($pat === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'support', 502);
                }
                if (!empty($pat['_status']) && (int)$pat['_status'] >= 400) {
                    $msgErr = (string)($pat['message'] ?? 'Could not update ticket.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msgErr, (int)$pat['_status']);
                    }
                    $this->Flash->error(__($msgErr));
                } else {
                    $this->portal->clear(['_tickets', 'tickets', 'threads']);
                    return $this->writeDone('Ticket updated.', 'support', ['status' => $norm]);
                }
            } elseif ($data !== []) {
                return $this->writeFail('Invalid support action.', 'support', 400);
            }
        }

        $this->releaseSession();
        $batch = $this->portal->getMulti([
            'tickets' => ['endpoint' => '/tickets', 'params' => [], 'ttl' => 120],
            'threads' => ['endpoint' => '/messages/threads', 'params' => [], 'ttl' => 120],
        ], $headers);
        $tRes = $batch['tickets'];
        if ($bounce = $this->bounceOnUnauth($tRes, '/admin/support')) return $bounce;
        $tickets = $tRes['data'] ?? (isset($tRes[0]) ? $tRes : []);
        if (!is_array($tickets)) $tickets = [];

        // Messages threads for admin (cached 60s)
        $mRes = $batch['threads'];
        $threads = $mRes['data'] ?? (isset($mRes[0]) ? $mRes : []);
        if (!is_array($threads)) $threads = [];

        $this->markDown($tRes, $mRes);
        $this->set(compact('userProfile', 'tickets', 'threads'));
    }

    public function reviews()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $this->releaseSession();
        $res = $this->portal->get('/admin/reviews', [], $headers, 300);
        if (empty($reviews = $res['data'] ?? null) && empty($res['_status'])) {
            // Fallback shape: some backends expose public /reviews.
            $alt = $this->portal->get('/reviews', [], $headers, 300);
            if (is_array($alt) && empty($alt['_status'])) {
                $res = $alt;
            }
        }
        $reviews = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($reviews)) $reviews = [];
        if (empty($reviews) && !empty($res) && empty($res['_status']) && isset($res['id'])) {
            $reviews = [$res];
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'reviews'));
    }

    public function verify(string $type = '', string $id = '')
    {
        if ($r = $this->requireAdmin()) return $r;
        if (!$this->getRequest()->is('post')) {
            if ($this->wantsJson()) {
                return $this->jsonErr('Method not allowed.', 405);
            }
            return $this->redirect(['action' => 'owners']);
        }
        $headers = $this->hostHeaders();
        $data = (array)$this->getRequest()->getData();
        $status = trim((string)($data['status'] ?? 'approved'));
        $reason = trim((string)($data['reason'] ?? $data['admin_notes'] ?? ''));
        $type = strtolower(trim($type));
        $id = trim($id);
        $isLodge = $type === 'lodge' || $type === 'property';

        // Backend ids are numeric today but may be UUIDs tomorrow — accept
        // anything sane. The old ctype_digit() check rejected every UUID row,
        // making ALL approve/reject buttons on those rows dead with a cryptic
        // "Invalid record id." flash that looked like trash UI.
        if ($id === '' || !preg_match('/^[A-Za-z0-9:_-]{1,64}$/', $id)) {
            return $this->writeFail('Invalid record id.', $isLodge ? 'lodges' : 'owners', 400);
        }

        // Backend validation:
        // owner: approved,rejected,changes_requested,suspended
        // lodge: Active,Pending,Removed,changes_requested,rejected
        if ($isLodge) {
            $map = [
                'approved' => 'Active',
                'approve' => 'Active',
                'active' => 'Active',
                'rejected' => 'rejected',
                'reject' => 'rejected',
                'changes_requested' => 'changes_requested',
                'pending' => 'Pending',
                'removed' => 'Removed',
                'suspended' => 'Removed',
            ];
            $low = strtolower($status);
            $status = $map[$low] ?? 'Active';
        } else {
            // owner: normalise to backend lowercase set
            $low = strtolower($status);
            $allowedOwner = ['approved', 'rejected', 'changes_requested', 'suspended'];
            $status = in_array($low, $allowedOwner, true) ? $low : 'approved';
            $type = 'owner';
        }
        if (($status === 'rejected' || $status === 'changes_requested') && $reason === '' && $this->wantsJson()) {
            return $this->jsonErr('A reason is required for this decision.', 422);
        }
        // Send every reason key the backend has ever accepted (reason,
        // admin_notes, notes). Older rows failed because the direct-JS path
        // sent only `reason` while some backend builds validate `admin_notes`,
        // and vice versa — both paths now send identical payloads.
        $payload = ['status' => $status];
        if ($reason !== '') {
            $payload['reason'] = $reason;
            $payload['admin_notes'] = $reason;
            $payload['notes'] = $reason;
        }

        // Release the session lock: the write below is the slowest part and
        // must not serialize parallel PJAX/prefetch on the session file.
        $this->releaseSession();
        // Method + path matrix: the spec says POST /admin/verification/…,
        // but backend builds have varied (PATCH-only, non-admin prefix).
        // 404/405 = "try next shape"; any other 4xx is a REAL validation
        // verdict (bad status value, missing reason) — stop and surface it
        // instead of masking it behind another shape's 404.
        $paths = $isLodge
            ? ['/admin/verification/lodge/' . $id, '/verification/lodge/' . $id]
            : ['/admin/verification/owner/' . $id, '/verification/owner/' . $id];
        $res = null;
        $tried = 0;
        foreach ($paths as $endpoint) {
            foreach (['post', 'patch'] as $verb) {
                $tried++;
                $attempt = $verb === 'post'
                    ? $this->apiClient->post($endpoint, $payload, $headers)
                    : $this->apiClient->patch($endpoint, $payload, $headers);
                if ($attempt === null) {
                    $res = null;
                    break 2; // transport dead: no point trying more shapes
                }
                $code = (int)($attempt['_status'] ?? 200);
                if (($code === 404 || $code === 405) && !($endpoint === end($paths) && $verb === 'patch')) {
                    continue; // try next shape
                }
                $res = $attempt;
                break 2;
            }
        }
        $back = $isLodge ? '/admin/lodges' : '/admin/owners';
        if ($bounce = $this->bounceOnUnauth($res, $back)) return $bounce;
        if ($res === null) {
            return $this->writeFail('Service unavailable. Please try again.', $isLodge ? 'lodges' : 'owners', 502);
        }
        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            $code = (int)$res['_status'];
            $msg = (string)($res['message'] ?? 'Verification failed.');
            if ($code >= 500) {
                // Backend itself is failing — say so plainly with NOT-saved,
                // or the admin will keep clicking a dead button thinking the
                // portal is trash. Never pretend success on 5xx.
                $msg = sprintf('Backend error (%d) — decision NOT saved. %s', $code, $msg);
            }
            if ($this->wantsJson()) {
                return $this->jsonErr($msg, $code);
            }
            $this->Flash->error(__($msg));
        } else {
            // Scoped bust only: a lodge decision must not cold the whole portal.
            $this->portal->clear($isLodge ? ['properties', 'dashboard', '_verif'] : ['users', 'owners', 'dashboard', '_verif']);
            return $this->writeDone('Verification updated.', $isLodge ? 'lodges' : 'owners', ['status' => $status]);
        }
        if ($isLodge) {
            return $this->redirect(['action' => 'lodges']);
        }
        return $this->redirect(['action' => 'owners']);
    }

    // ---- Ported from fastnet_admin_portal (real working) ----

    public function bookings()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $bid = trim((string)($data['booking_id'] ?? ''));
            $status = trim((string)($data['status'] ?? ''));
            if ($bid === '' || $status === '') {
                return $this->writeFail('Booking and status are required.', 'bookings', 400);
            }
            $allowed = ['Pending', 'Confirmed', 'Checked In', 'Completed', 'Cancelled'];
            $norm = null;
            foreach ($allowed as $a) {
                if (strcasecmp($a, $status) === 0) {
                    $norm = $a;
                    break;
                }
            }
            if ($norm === null) {
                return $this->writeFail('Invalid booking status.', 'bookings', 400);
            }
            // Release the session lock before slow backend I/O.
            $this->releaseSession();
            // Backend source of truth with alias fallback.
            $candidates = ['/admin/bookings/' . $bid . '/status', '/bookings/' . $bid . '/status'];
            $res = null;
            foreach ($candidates as $ep) {
                $attempt = $this->apiClient->patch($ep, ['status' => $norm], $headers);
                if ($attempt === null) {
                    $res = null;
                    break;
                }
                if ((int)($attempt['_status'] ?? 200) === 404 && $ep !== end($candidates)) {
                    continue;
                }
                $res = $attempt;
                break;
            }
            if ($bounce = $this->bounceOnUnauth($res, '/admin/bookings')) return $bounce;
            if ($res === null) {
                return $this->writeFail('Service unavailable. Please try again.', 'bookings', 502);
            }
            if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                $msg = (string)($res['message'] ?? 'Could not update booking.');
                if ($this->wantsJson()) {
                    return $this->jsonErr($msg, (int)$res['_status']);
                }
                $this->Flash->error(__($msg));
            } else {
                $this->portal->clear(['_bookings', 'bookings', 'finance', 'dashboard']);
                return $this->writeDone('Booking status updated.', 'bookings', ['status' => $norm]);
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        // Backend owns filtering — search/status go to the API (cached 30s).
        $listParams = [];
        if ($search !== '') $listParams['search'] = $search;
        if ($status !== '' && $status !== 'all') $listParams['status'] = $status;

        $this->releaseSession();
        $res = $this->portal->get('/admin/bookings', $listParams, $headers, 30);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/bookings')) return $bounce;
        $bookings = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($bookings)) $bookings = [];
        // Fallback to generic bookings
        if (empty($bookings) && !empty($res['_status'])) {
            $r2 = $this->portal->get('/bookings', $listParams, $headers, 30);
            $bookings = $r2['data'] ?? (isset($r2[0]) ? $r2 : []);
            if (!is_array($bookings)) $bookings = [];
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'bookings', 'search', 'status'));
    }

    public function staff()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch','put','delete'])) {
            $this->releaseSession();
            $data = (array)$this->getRequest()->getData();
            // Native DELETE (direct path) carries no action field.
            $method = strtolower($this->getRequest()->getMethod());
            $action = trim((string)($data['action'] ?? ''));
            if ($action === '' && $method === 'delete') {
                $action = 'delete';
            }
            if ($action === 'add' || $action === 'update') {
                $payload = [
                    'name' => trim((string)($data['name'] ?? '')),
                    'role' => trim((string)($data['role'] ?? 'Receptionist')),
                    'phone' => trim((string)($data['phone'] ?? '')),
                ];
                $allowedRoles = ['Manager', 'Receptionist', 'Housekeeper', 'Maintenance'];
                if (!in_array($payload['role'], $allowedRoles, true)) {
                    $payload['role'] = 'Receptionist';
                }
                if ($payload['name'] === '' || $payload['phone'] === '') {
                    return $this->writeFail('Name and phone are required.', 'staff', 400);
                }
                if (strlen($payload['phone']) < 7) {
                    return $this->writeFail('Enter a valid phone number.', 'staff', 400);
                }
                if ($action === 'update' && !empty($data['staff_id'])) {
                    $sid = trim((string)$data['staff_id']);
                    if ($sid === '') {
                        return $this->writeFail('Staff id is required.', 'staff', 400);
                    }
                    $res = $this->apiClient->patch('/staff/' . $sid, $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                    if ($res === null) {
                        return $this->writeFail('Service unavailable. Please try again.', 'staff', 502);
                    }
                    if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                        $msg = (string)($res['message'] ?? 'Could not update staff.');
                        if ($this->wantsJson()) {
                            return $this->jsonErr($msg, (int)$res['_status']);
                        }
                        $this->Flash->error(__($msg));
                    } else {
                        $this->portal->clear(['_staff', 'staff']);
                        return $this->writeDone('Staff updated.', 'staff');
                    }
                } else {
                    $res = $this->apiClient->post('/staff', $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                    if ($res === null) {
                        return $this->writeFail('Service unavailable. Please try again.', 'staff', 502);
                    }
                    if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                        $msg = (string)($res['message'] ?? 'Could not add staff.');
                        if ($this->wantsJson()) {
                            return $this->jsonErr($msg, (int)$res['_status']);
                        }
                        $this->Flash->error(__($msg));
                    } else {
                        $this->portal->clear(['_staff', 'staff']);
                        return $this->writeDone('Staff added.', 'staff');
                    }
                }
            } elseif ($action === 'delete' && !empty($data['staff_id'])) {
                $sid = trim((string)$data['staff_id']);
                if ($sid === '') {
                    return $this->writeFail('Staff id is required.', 'staff', 400);
                }
                $res = $this->apiClient->delete('/staff/' . $sid, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                if ($res === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'staff', 502);
                }
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $msg = (string)($res['message'] ?? 'Could not delete staff.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msg, (int)$res['_status']);
                    }
                    $this->Flash->error(__($msg));
                } else {
                    $this->portal->clear(['_staff', 'staff']);
                    return $this->writeDone('Staff deleted.', 'staff');
                }
            } elseif ($action !== '') {
                return $this->writeFail('Unknown staff action.', 'staff', 400);
            }
        }

        $this->releaseSession();
        $res = $this->portal->get('/staff', [], $headers, 180);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
        $staff = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($staff)) $staff = [];

        $this->markDown($res);
        $this->set(compact('userProfile', 'staff'));
    }

    public function lodgeRequests()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $rid = trim((string)($data['request_id'] ?? ''));
            $status = trim((string)($data['status'] ?? ''));
            if ($rid === '' || $status === '') {
                if ($this->getRequest()->is(['post','patch']) && ($data !== [])) {
                    return $this->writeFail('Request and status are required.', 'lodgeRequests', 400);
                }
            } else {
                $allowed = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
                $norm = null;
                foreach ($allowed as $a) {
                    if (strcasecmp($a, $status) === 0) {
                        $norm = $a;
                        break;
                    }
                }
                if ($norm === null) {
                    return $this->writeFail('Invalid request status.', 'lodgeRequests', 400);
                }
                $this->releaseSession();
                // Backend source of truth with alias fallback.
                $candidates = ['/lodge-requests/' . $rid . '/status', '/admin/lodge-requests/' . $rid . '/status'];
                $res = null;
                foreach ($candidates as $ep) {
                    $attempt = $this->apiClient->patch($ep, ['status' => $norm], $headers);
                    if ($attempt === null) {
                        $res = null;
                        break;
                    }
                    if ((int)($attempt['_status'] ?? 200) === 404 && $ep !== end($candidates)) {
                        continue;
                    }
                    $res = $attempt;
                    break;
                }
                if ($bounce = $this->bounceOnUnauth($res, '/admin/requests')) return $bounce;
                if ($res === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'lodgeRequests', 502);
                }
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $msg = (string)($res['message'] ?? 'Could not update request.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msg, (int)$res['_status']);
                    }
                    $this->Flash->error(__($msg));
                } else {
                    $this->portal->clear(['_lodge_requests', 'lodge-requests', 'properties', 'dashboard']);
                    return $this->writeDone('Request updated.', 'lodgeRequests', ['status' => $norm]);
                }
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));
        $type = trim((string)($q['type'] ?? ''));

        // Backend owns filtering (cached 30s per filter combo).
        $listParams = [];
        if ($search !== '') $listParams['search'] = $search;
        if ($status !== '' && $status !== 'all') $listParams['status'] = $status;
        if ($type !== '' && $type !== 'all') $listParams['type'] = $type;

        $this->releaseSession();
        $res = $this->portal->get('/lodge-requests', $listParams, $headers, 30);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/requests')) return $bounce;
        $requests = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($requests)) $requests = [];

        // Dynamic type options for filter
        $typeOptions = ['all'];
        foreach ($requests as $r) {
            $t = trim((string)($r['type'] ?? $r['room_type'] ?? ''));
            if ($t !== '' && !in_array($t, $typeOptions)) $typeOptions[] = $t;
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'requests', 'search', 'status', 'type', 'typeOptions'));
    }
}
