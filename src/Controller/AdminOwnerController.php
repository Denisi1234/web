<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\RoleService;
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

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
        $this->roleService = new RoleService($this->apiClient, $this->authService);
    }

    private function hostHeaders(): array
    {
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        return $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
    }

    private function requireAdmin(): ?\Cake\Http\Response
    {
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        $user = $this->getRequest()->getSession()->read('User');
        if ($token === '' || empty($user)) {
            $this->Flash->error(__('Please sign in to access Admin.'));
            return $this->redirect('/login');
        }
        $role = $this->roleService->getRole($token);
        if ($role !== 'admin') {
            // Owners go to host, others to home
            if ($role === 'owner') {
                return $this->redirect('/host/dashboard');
            }
            $this->Flash->error(__('Admin access required.'));
            return $this->redirect('/');
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
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $propRes = $this->apiClient->get('/admin/properties', [], $headers);
        if (empty($propRes) || !empty($propRes['_status'])) {
            $propRes = $this->apiClient->get('/properties', [], $headers);
        }
        $properties = $propRes['data'] ?? (isset($propRes[0]) ? $propRes : []);
        if (!is_array($properties)) $properties = [];

        $bookRes = $this->apiClient->get('/admin/bookings', [], $headers);
        $bookings = $bookRes['data'] ?? (isset($bookRes[0]) ? $bookRes : []);
        if (!is_array($bookings)) $bookings = [];

        $userRes = $this->apiClient->get('/admin/users', ['role' => 'owner'], $headers);
        $owners = $userRes['data'] ?? (isset($userRes[0]) ? $userRes : []);
        if (!is_array($owners)) $owners = [];

        $stats = [
            'properties' => count($properties),
            'bookings' => count($bookings),
            'owners' => count($owners),
            'revenue' => array_sum(array_map(fn($b) => (float)($b['total_price'] ?? 0), $bookings)),
        ];
        $stats['platform_fee'] = $stats['revenue'] * 0.10;
        $stats['owner_earnings'] = $stats['revenue'] * 0.90;

        // Recent properties for table
        $recentProperties = array_slice($properties, 0, 8);
        $this->set(compact('userProfile', 'properties', 'recentProperties', 'bookings', 'owners', 'stats'));
    }

    public function owners()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $q = $this->getRequest()->getQueryParams();
        $page = max(1, (int)($q['page'] ?? 1));
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $params = ['role' => 'owner', 'page' => $page, 'per_page' => 15];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        // Try admin users + financial summary for richer data
        $res = $this->apiClient->get('/admin/users', $params, $headers);
        $users = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($users)) $users = [];

        $finRes = $this->apiClient->get('/admin/owners/financial-summary', $params, $headers);
        $financial = $finRes['data'] ?? $finRes;
        if (!is_array($financial)) $financial = [];

        $this->set(compact('userProfile', 'users', 'financial', 'search', 'status', 'page'));
    }

    public function lodges()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $params = ['per_page' => 50];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        $res = $this->apiClient->get('/admin/properties', $params, $headers);
        $properties = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($properties)) $properties = [];
        if (empty($properties) && !empty($res) && empty($res['_status'])) $properties = $res;

        $this->set(compact('userProfile', 'properties', 'search', 'status'));
    }

    public function financeOverview()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $q = $this->getRequest()->getQueryParams();
        $dateRange = trim((string)($q['date_range'] ?? '30_days'));
        $params = ['date_range' => $dateRange];
        if (!empty($q['date_from'])) $params['date_from'] = $q['date_from'];
        if (!empty($q['date_to'])) $params['date_to'] = $q['date_to'];

        $res = $this->apiClient->get('/finance/overview', $params, $headers);
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->apiClient->get('/admin/dashboard-stats', $params, $headers);
        }
        $finance = $res['data'] ?? $res;
        if (!is_array($finance)) $finance = [];

        $this->set(compact('userProfile', 'finance', 'dateRange'));
    }

    public function financeLedger()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $q = $this->getRequest()->getQueryParams();
        $params = [];
        foreach (['search','transaction_id','booking_reference','min_amount','max_amount','date_from','date_to','owner_id','property_id','transaction_type','payment_status','payout_status','sort_by','page','per_page'] as $k) {
            if (isset($q[$k]) && $q[$k] !== '') $params[$k] = $q[$k];
        }
        if (empty($params['per_page'])) $params['per_page'] = 15;
        if (empty($params['page'])) $params['page'] = 1;

        $res = $this->apiClient->get('/finance/ledger', $params, $headers);
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
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

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
                if (!empty($up['_status']) && (int)$up['_status'] >= 400) {
                    $this->Flash->error(__($up['message'] ?? 'Could not update payout.'));
                } else {
                    $this->Flash->success(__('Payout updated.'));
                    return $this->redirect(['action' => 'payouts']);
                }
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $status = trim((string)($q['status'] ?? ''));
        $params = [];
        if ($status !== '') $params['status'] = $status;

        $res = $this->apiClient->get('/payouts', $params, $headers);
        $payouts = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($payouts)) $payouts = [];

        // Also fetch finance overview for cards
        $fin = $this->apiClient->get('/finance/overview', [], $headers);
        $finance = $fin['data'] ?? $fin;
        if (!is_array($finance)) $finance = [];

        $this->set(compact('userProfile', 'payouts', 'finance', 'status'));
    }

    public function support()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $action = trim((string)($data['action'] ?? ''));
            if ($action === 'reply' && !empty($data['ticket_id'])) {
                $tid = (int)$data['ticket_id'];
                $msg = trim((string)($data['message'] ?? ''));
                if ($msg !== '') {
                    $rep = $this->apiClient->post('/tickets/' . $tid . '/reply', ['message' => $msg], $headers);
                    if (!empty($rep['_status']) && (int)$rep['_status'] >= 400) {
                        $this->Flash->error(__($rep['message'] ?? 'Could not send reply.'));
                    } else {
                        $this->Flash->success(__('Reply sent.'));
                        return $this->redirect(['action' => 'support']);
                    }
                }
            } elseif ($action === 'status' && !empty($data['ticket_id'])) {
                $tid = (int)$data['ticket_id'];
                $st = trim((string)($data['status'] ?? ''));
                $pat = $this->apiClient->patch('/tickets/' . $tid . '/status', ['status' => $st], $headers);
                if (!empty($pat['_status']) && (int)$pat['_status'] >= 400) {
                    $this->Flash->error(__($pat['message'] ?? 'Could not update ticket.'));
                } else {
                    $this->Flash->success(__('Ticket updated.'));
                    return $this->redirect(['action' => 'support']);
                }
            }
        }

        $tRes = $this->apiClient->get('/tickets', [], $headers);
        $tickets = $tRes['data'] ?? (isset($tRes[0]) ? $tRes : []);
        if (!is_array($tickets)) $tickets = [];

        // Messages threads for admin
        $mRes = $this->apiClient->get('/messages/threads', [], $headers);
        $threads = $mRes['data'] ?? (isset($mRes[0]) ? $mRes : []);
        if (!is_array($threads)) $threads = [];

        $this->set(compact('userProfile', 'tickets', 'threads'));
    }

    public function reviews()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $res = $this->apiClient->get('/admin/reviews', [], $headers);
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
        $payload = ['status' => $status];
        if ($reason !== '') $payload['reason'] = $reason;
        if ($reason !== '') $payload['admin_notes'] = $reason;

        $type = strtolower(trim($type));
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
        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            $this->Flash->error(__($res['message'] ?? 'Verification failed.'));
        } else {
            $this->Flash->success(__('Verification updated.'));
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
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $bid = trim((string)($data['booking_id'] ?? ''));
            $status = trim((string)($data['status'] ?? ''));
            if ($bid !== '' && $status !== '') {
                // fastnet_admin_portal: PATCH /admin/bookings/{id}/status — backend may use admin/bookings
                $res = $this->apiClient->patch('/admin/bookings/' . $bid . '/status', ['status' => $status], $headers);
                if (empty($res) || !empty($res['_status'])) {
                    // Fallback to bookings status
                    $res2 = $this->apiClient->patch('/bookings/' . $bid . '/status', ['status' => $status], $headers);
                    $res = $res2 ?? $res;
                }
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update booking.'));
                } else if ($res !== null) {
                    $this->Flash->success(__('Booking status updated.'));
                    return $this->redirect(['action' => 'bookings']);
                }
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $res = $this->apiClient->get('/admin/bookings', [], $headers);
        $bookings = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($bookings)) $bookings = [];
        // Fallback to generic bookings
        if (empty($bookings) && !empty($res['_status'])) {
            $r2 = $this->apiClient->get('/bookings', [], $headers);
            $bookings = $r2['data'] ?? (isset($r2[0]) ? $r2 : []);
            if (!is_array($bookings)) $bookings = [];
        }

        // Search/status filter (client-side mirror fastnet_admin_portal bookings_screen.dart)
        if ($search !== '') {
            $low = strtolower($search);
            $bookings = array_values(array_filter($bookings, fn($b) => str_contains(strtolower(json_encode($b)), $low)));
        }
        if ($status !== '' && $status !== 'all') {
            $bookings = array_values(array_filter($bookings, fn($b) => strtolower((string)($b['status'] ?? $b['payment_status'] ?? '')) === strtolower($status)));
        }

        $this->set(compact('userProfile', 'bookings', 'search', 'status'));
    }

    public function staff()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

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
                        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                            $this->Flash->error(__($res['message'] ?? 'Could not update staff.'));
                        } else {
                            $this->Flash->success(__('Staff updated.'));
                            return $this->redirect(['action' => 'staff']);
                        }
                    } else {
                        $res = $this->apiClient->post('/staff', $payload, $headers);
                        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                            $this->Flash->error(__($res['message'] ?? 'Could not add staff.'));
                        } else {
                            $this->Flash->success(__('Staff added.'));
                            return $this->redirect(['action' => 'staff']);
                        }
                    }
                }
            } elseif ($action === 'delete' && !empty($data['staff_id'])) {
                $sid = trim((string)$data['staff_id']);
                $res = $this->apiClient->delete('/staff/' . $sid, $headers);
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not delete staff.'));
                } else {
                    $this->Flash->success(__('Staff deleted.'));
                    return $this->redirect(['action' => 'staff']);
                }
            }
        }

        $res = $this->apiClient->get('/staff', [], $headers);
        $staff = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($staff)) $staff = [];

        $this->set(compact('userProfile', 'staff'));
    }

    public function lodgeRequests()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $rid = trim((string)($data['request_id'] ?? ''));
            $status = trim((string)($data['status'] ?? ''));
            if ($rid !== '' && $status !== '') {
                // Real PATCH if backend supports; fallback to local optimistic not needed
                $res = $this->apiClient->patch('/lodge-requests/' . $rid . '/status', ['status' => $status], $headers);
                if (empty($res) || !empty($res['_status'])) {
                    // Try alternative
                    $res2 = $this->apiClient->patch('/lodge-requests/' . $rid, ['status' => $status], $headers);
                    $res = $res2 ?? $res;
                }
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update request.'));
                } else if ($res !== null) {
                    $this->Flash->success(__('Request updated.'));
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

        $res = $this->apiClient->get('/lodge-requests', [], $headers);
        $requests = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($requests)) $requests = [];

        if ($search !== '') {
            $low = strtolower($search);
            $requests = array_values(array_filter($requests, fn($r) => str_contains(strtolower(json_encode($r)), $low)));
        }
        if ($status !== '' && $status !== 'all') {
            $requests = array_values(array_filter($requests, fn($r) => strtolower((string)($r['status'] ?? '')) === strtolower($status)));
        }
        if ($type !== '' && $type !== 'all') {
            $requests = array_values(array_filter($requests, fn($r) => strtolower((string)($r['type'] ?? $r['room_type'] ?? '')) === strtolower($type)));
        }

        // Dynamic type options for filter
        $typeOptions = ['all'];
        foreach ($requests as $r) {
            $t = trim((string)($r['type'] ?? $r['room_type'] ?? ''));
            if ($t !== '' && !in_array($t, $typeOptions)) $typeOptions[] = $t;
        }

        $this->set(compact('userProfile', 'requests', 'search', 'status', 'type', 'typeOptions'));
    }
}
