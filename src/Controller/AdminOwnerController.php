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
     * Session-independent profile (120s shared cache) — backend
     * /user/personal-details takes ~2.3s; sidebar/topbar only need it
     * for display. No session reads/writes: works on any instance,
     * survives session loss. Backend stays source of truth on refresh.
     */
    private function cachedProfile(): array
    {
        $token = $this->rawToken();
        if ($token === '') return [];
        $key = 'portal_profile_' . md5($token);
        if (isset(self::$profileMemo[$key])) {
            return self::$profileMemo[$key];
        }
        try {
            $hit = Cache::read($key, 'default');
            if (is_array($hit) && isset($hit['exp'], $hit['data']) && $hit['exp'] > time() && !empty($hit['data'])) {
                self::$profileMemo[$key] = $hit['data'];
                return $hit['data'];
            }
        } catch (\Throwable $e) {
        }
        $fresh = $this->authService->getPersonalDetails($token);
        if (!empty($fresh) && !empty($fresh['id'])) { // id required: public personal-details returns a demo profile (null id) for bad tokens
            $entry = ['exp' => time() + 120, 'data' => $fresh];
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
        return $fresh;
    }

    /**
     * Admin gate.
     *
     * This used to return null unconditionally ("no login wall"), so every
     * /admin/* page shell rendered for any visitor. The backend 403s the data
     * calls, so nothing leaked, but the portal rendered empty admin pages to
     * the public. Now a non-admin is refused with a real 403.
     */
    private function requireAdmin(): ?\Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $user = $session->read('User');
        $role = strtolower((string)(is_array($user) ? ($user['role'] ?? '') : ''));

        if ($role === 'admin') {
            return null;
        }

        // Not signed in at all -> send to login with a safe return path.
        if ($session->read('is_logged_out') || empty($user)) {
            $this->Flash->error(__('Sign in to access the admin portal.'));
            return $this->redirect('/login?redirect=' . urlencode('/admin/dashboard'));
        }

        // Signed in but not an admin -> refuse with the branded page (HTTP 403),
// not a bare exception the renderer would map to error400.
        return $this->refusePortalAccess(
            '/admin/dashboard',
            __('You do not have access to the admin portal.')
        );
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

        // Backend owns aggregation; reads cached 60s (was 3 sequential HTTP calls per view).
        $propRes = $this->portal->get('/admin/properties', [], $headers, 60);
        if ($bounce = $this->bounceOnUnauth($propRes, '/admin/dashboard')) return $bounce;
        if (empty($propRes) || !empty($propRes['_status'])) {
            $propRes = $this->portal->get('/properties', [], $headers, 60);
        }
        $properties = $propRes['data'] ?? (isset($propRes[0]) ? $propRes : []);
        if (!is_array($properties)) $properties = [];

        $bookRes = $this->portal->get('/admin/bookings', [], $headers, 60);
        $bookings = $bookRes['data'] ?? (isset($bookRes[0]) ? $bookRes : []);
        if (!is_array($bookings)) $bookings = [];

        $userRes = $this->portal->get('/admin/users', ['role' => 'owner'], $headers, 60);
        $owners = $userRes['data'] ?? (isset($userRes[0]) ? $userRes : []);
        if (!is_array($owners)) $owners = [];

        // Verification queue counts come from one purpose-built aggregate rather
        // than being derived client-side from the full property/booking/user
        // collections above.
        $verRes = $this->portal->get('/admin/verification/summary', [], $headers, 60);
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

        $q = $this->getRequest()->getQueryParams();
        $page = max(1, (int)($q['page'] ?? 1));
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $params = ['role' => 'owner', 'page' => $page, 'per_page' => 15];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        // Try admin users + financial summary for richer data (cached 30s)
        $res = $this->portal->get('/admin/users', $params, $headers, 30);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/owners')) return $bounce;
        $users = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($users)) $users = [];

        $finRes = $this->portal->get('/admin/owners/financial-summary', $params, $headers, 30);
        $financial = $finRes['data'] ?? $finRes;
        if (!is_array($financial)) $financial = [];

        $this->set(compact('userProfile', 'users', 'financial', 'search', 'status', 'page'));
    }

    public function lodges()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $params = ['per_page' => 50];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        $res = $this->portal->get('/admin/properties', $params, $headers, 60);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/lodges')) return $bounce;
        $properties = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($properties)) $properties = [];
        if (empty($properties) && !empty($res) && empty($res['_status'])) $properties = $res;

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

        $res = $this->portal->get('/finance/overview', $params, $headers, 120);
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->portal->get('/admin/dashboard-stats', $params, $headers, 120);
        }
        $finance = $res['data'] ?? $res;
        if (!is_array($finance)) $finance = [];

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

        $res = $this->portal->get('/finance/ledger', $params, $headers, 30);
        $ledger = $res['data'] ?? $res;
        if (!is_array($ledger)) $ledger = [];
        $transactions = $ledger['data'] ?? $ledger;
        if (!is_array($transactions)) $transactions = [];

        // Keep raw for pagination meta
        $meta = $ledger['meta'] ?? null;

        $this->set(compact('userProfile', 'transactions', 'ledger', 'meta', 'params'));
    }

    public function payouts()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            // Handle payout status update via same page POST
            if (!empty($data['payout_id']) && !empty($data['status'])) {
                $id = (int)$data['payout_id'];
                $status = trim((string)$data['status']);
                $notes = trim((string)($data['notes'] ?? ''));
                $payload = ['status' => $status];
                if ($notes !== '') $payload['notes'] = $notes;
                $up = $this->apiClient->patch('/payouts/' . $id . '/status', $payload, $headers);
                if ($bounce = $this->bounceOnUnauth($up, '/admin/finance/payouts')) return $bounce;
                if (!empty($up['_status']) && (int)$up['_status'] >= 400) {
                    $this->Flash->error(__($up['message'] ?? 'Could not update payout.'));
                } else {
                    $this->Flash->success(__('Payout updated.'));
                    $this->portal->clear(['_payouts', 'finance', 'dashboard']);
                    return $this->redirect(['action' => 'payouts']);
                }
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $status = trim((string)($q['status'] ?? ''));
        $params = [];
        if ($status !== '') $params['status'] = $status;

        $res = $this->portal->get('/payouts', $params, $headers, 30);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/finance/payouts')) return $bounce;
        $payouts = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($payouts)) $payouts = [];

        // Also fetch finance overview for cards (cached 120s)
        $fin = $this->portal->get('/finance/overview', [], $headers, 120);
        $finance = $fin['data'] ?? $fin;
        if (!is_array($finance)) $finance = [];

        $this->set(compact('userProfile', 'payouts', 'finance', 'status'));
    }

    public function support()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $action = trim((string)($data['action'] ?? ''));
            if ($action === 'reply' && !empty($data['ticket_id'])) {
                $tid = (int)$data['ticket_id'];
                $msg = trim((string)($data['message'] ?? ''));
                if ($msg !== '') {
                    $rep = $this->apiClient->post('/tickets/' . $tid . '/reply', ['message' => $msg], $headers);
                    if ($bounce = $this->bounceOnUnauth($rep, '/admin/support')) return $bounce;
                    if (!empty($rep['_status']) && (int)$rep['_status'] >= 400) {
                        $this->Flash->error(__($rep['message'] ?? 'Could not send reply.'));
                    } else {
                        $this->Flash->success(__('Reply sent.'));
                        $this->portal->clear('_tickets');
                        return $this->redirect(['action' => 'support']);
                    }
                }
            } elseif ($action === 'status' && !empty($data['ticket_id'])) {
                $tid = (int)$data['ticket_id'];
                $st = trim((string)($data['status'] ?? ''));
                $pat = $this->apiClient->patch('/tickets/' . $tid . '/status', ['status' => $st], $headers);
                if ($bounce = $this->bounceOnUnauth($pat, '/admin/support')) return $bounce;
                if (!empty($pat['_status']) && (int)$pat['_status'] >= 400) {
                    $this->Flash->error(__($pat['message'] ?? 'Could not update ticket.'));
                } else {
                    $this->Flash->success(__('Ticket updated.'));
                    $this->portal->clear('_tickets');
                    return $this->redirect(['action' => 'support']);
                }
            }
        }

        $tRes = $this->portal->get('/tickets', [], $headers, 60);
        if ($bounce = $this->bounceOnUnauth($tRes, '/admin/support')) return $bounce;
        $tickets = $tRes['data'] ?? (isset($tRes[0]) ? $tRes : []);
        if (!is_array($tickets)) $tickets = [];

        // Messages threads for admin (cached 60s)
        $mRes = $this->portal->get('/messages/threads', [], $headers, 60);
        $threads = $mRes['data'] ?? (isset($mRes[0]) ? $mRes : []);
        if (!is_array($threads)) $threads = [];

        $this->set(compact('userProfile', 'tickets', 'threads'));
    }

    public function reviews()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $res = $this->portal->get('/admin/reviews', [], $headers, 120);
        $reviews = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($reviews)) $reviews = [];

        $this->set(compact('userProfile', 'reviews'));
    }

    public function verify(string $type = '', string $id = '')
    {
        if ($r = $this->requireAdmin()) return $r;
        if (!$this->getRequest()->is('post')) {
            return $this->redirect(['action' => 'owners']);
        }
        $headers = $this->hostHeaders();
        $data = (array)$this->getRequest()->getData();
        $status = trim((string)($data['status'] ?? 'approved'));
        $reason = trim((string)($data['reason'] ?? $data['admin_notes'] ?? ''));
        $type = strtolower(trim($type));

        // Backend validation:
        // owner: approved,rejected,changes_requested,suspended
        // lodge: Active,Pending,Removed,changes_requested,rejected
        if ($type === 'lodge' || $type === 'property') {
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
            $status = $map[$low] ?? $map[strtolower(trim($status))] ?? 'Active';
        } else {
            // owner: normalise to backend lowercase set
            $low = strtolower($status);
            $allowedOwner = ['approved', 'rejected', 'changes_requested', 'suspended'];
            $status = in_array($low, $allowedOwner, true) ? $low : 'approved';
        }
        $payload = ['status' => $status];
        if ($reason !== '') $payload['reason'] = $reason;
        if ($reason !== '') $payload['admin_notes'] = $reason;

        $endpoint = '';
        if ($type === 'owner') {
            $endpoint = '/admin/verification/owner/' . $id;
        } elseif ($type === 'lodge' || $type === 'property') {
            $endpoint = '/admin/verification/lodge/' . $id;
        } else {
            $this->Flash->error(__('Invalid verification type.'));
            return $this->redirect(['action' => 'owners']);
        }
        $res = $this->apiClient->post($endpoint, $payload, $headers);
        $back = ($type === 'lodge' || $type === 'property') ? '/admin/lodges' : '/admin/owners';
        if ($bounce = $this->bounceOnUnauth($res, $back)) return $bounce;
        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            $this->Flash->error(__($res['message'] ?? 'Verification failed.'));
        } else {
            $this->Flash->success(__('Verification updated.'));
            $this->portal->clear();
        }
        if ($type === 'lodge' || $type === 'property') {
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
            if ($bid !== '' && $status !== '') {
                // Backend source of truth: PATCH /admin/bookings/{id}/status
                $res = $this->apiClient->patch('/admin/bookings/' . $bid . '/status', ['status' => $status], $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/admin/bookings')) return $bounce;
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update booking.'));
                } else if ($res !== null) {
                    $this->Flash->success(__('Booking status updated.'));
                    $this->portal->clear(['_bookings', 'finance', 'dashboard']);
                    return $this->redirect(['action' => 'bookings']);
                }
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        // Backend owns filtering — search/status go to the API (cached 30s).
        $listParams = [];
        if ($search !== '') $listParams['search'] = $search;
        if ($status !== '' && $status !== 'all') $listParams['status'] = $status;

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

        $this->set(compact('userProfile', 'bookings', 'search', 'status'));
    }

    public function staff()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $action = trim((string)($data['action'] ?? ''));
            if ($action === 'add' || $action === 'update') {
                $payload = [
                    'name' => trim((string)($data['name'] ?? '')),
                    'role' => trim((string)($data['role'] ?? 'Receptionist')),
                    'phone' => trim((string)($data['phone'] ?? '')),
                ];
                if ($payload['name'] === '' || $payload['phone'] === '') {
                    $this->Flash->error(__('Name and phone are required.'));
                } else {
                    if ($action === 'update' && !empty($data['staff_id'])) {
                        $sid = trim((string)$data['staff_id']);
                        $res = $this->apiClient->patch('/staff/' . $sid, $payload, $headers);
                        if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                            $this->Flash->error(__($res['message'] ?? 'Could not update staff.'));
                        } else {
                            $this->Flash->success(__('Staff updated.'));
                            $this->portal->clear('_staff');
                            return $this->redirect(['action' => 'staff']);
                        }
                    } else {
                        $res = $this->apiClient->post('/staff', $payload, $headers);
                        if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                            $this->Flash->error(__($res['message'] ?? 'Could not add staff.'));
                        } else {
                            $this->Flash->success(__('Staff added.'));
                            $this->portal->clear('_staff');
                            return $this->redirect(['action' => 'staff']);
                        }
                    }
                }
            } elseif ($action === 'delete' && !empty($data['staff_id'])) {
                $sid = trim((string)$data['staff_id']);
                $res = $this->apiClient->delete('/staff/' . $sid, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not delete staff.'));
                } else {
                    $this->Flash->success(__('Staff deleted.'));
                    $this->portal->clear('_staff');
                    return $this->redirect(['action' => 'staff']);
                }
            }
        }

        $res = $this->portal->get('/staff', [], $headers, 60);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
        $staff = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($staff)) $staff = [];

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
            if ($rid !== '' && $status !== '') {
                // Backend source of truth: PATCH /lodge-requests/{id}/status
                $res = $this->apiClient->patch('/lodge-requests/' . $rid . '/status', ['status' => $status], $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/admin/requests')) return $bounce;
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update request.'));
                } else if ($res !== null) {
                    $this->Flash->success(__('Request updated.'));
                    $this->portal->clear(['_lodge_requests', 'properties', 'dashboard']);
                    return $this->redirect(['action' => 'lodgeRequests']);
                } else {
                    $this->Flash->error(__('Lodge requests update not yet supported by backend — showing local.'));
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

        $this->set(compact('userProfile', 'requests', 'search', 'status', 'type', 'typeOptions'));
    }
}
