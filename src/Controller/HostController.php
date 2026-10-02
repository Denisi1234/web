<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use Cake\Http\Response;

/**
 * HostController — Owner portal adapted to web + mobile.
 * Proxies to fastnet_backend same as admin_owner_portal (index.php:12) and mobile host_dashboard.
 * Tokens: r16, shadow 0 6 16, #2563EB, #C2410C, #F8FAFC.
 */
class HostController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected AuthService $authService;

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

    /**
     * Owner-scoped property list, backend as source of truth.
     * Admin → /admin/properties (all, has host_id).
     * Owner → /properties?mine=1 (own only, incl. pending/roomless).
     * Falls back to client-side host_id filter when items carry it.
     * Caches in session for 60s to make portal navigations instantaneous.
     */
    private function myProperties(array $headers): array
    {
        if ($this->rawToken() === '') return [];
        $session = $this->getRequest()->getSession();
        $cached = $session->read('HostProperties');
        $ts = (int)$session->read('HostPropertiesTs');
        if (is_array($cached) && !empty($cached) && $ts > time() - 60) {
            return $cached;
        }

        if ($this->isAdminUser()) {
            $this->releaseSession();
            $res = $this->apiClient->get('/admin/properties', [], $headers);
            $this->markBackend($res);
            if ($res === null) return is_array($cached) ? $cached : [];
            if (!empty($res) && empty($res['_status'])) {
                $list = $res['data'] ?? (isset($res[0]) ? $res : []);
                if (is_array($list)) {
                    $result = array_values($list);
                    $session->write('HostProperties', $result);
                    $session->write('HostPropertiesTs', time());
                    return $result;
                }
            }
        } else {
            $this->releaseSession();
            $res = $this->apiClient->get('/properties', ['mine' => 1, 'per_page' => 50], $headers);
            $this->markBackend($res);
            if ($res === null) return is_array($cached) ? $cached : [];
            if (!empty($res) && empty($res['_status'])) {
                $list = $res['data'] ?? (isset($res[0]) ? $res : []);
                if (is_array($list)) {
                    $result = array_values($list);
                    $session->write('HostProperties', $result);
                    $session->write('HostPropertiesTs', time());
                    return $result;
                }
            }
        }
        // Fallback: unscoped list filtered by host_id when present
        $this->releaseSession();
        $res = $this->apiClient->get('/properties', ['per_page' => 50], $headers);
        $this->markBackend($res);
        if ($res === null) return is_array($cached) ? $cached : [];
        $list = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($list)) return is_array($cached) ? $cached : [];
        $oid = $this->ownerId();
        if (!$this->isAdminUser() && $oid > 0) {
            $scoped = array_values(array_filter($list, fn($p) => (int)($p['host_id'] ?? $p['host']['id'] ?? 0) === $oid));
            // Only use filtered result if items actually carry ownership info
            foreach ($list as $p) {
                if (isset($p['host_id']) || isset($p['host']['id'])) {
                    $session->write('HostProperties', $scoped);
                    $session->write('HostPropertiesTs', time());
                    return $scoped;
                }
            }
        }
        $result = array_values($list);
        $session->write('HostProperties', $result);
        $session->write('HostPropertiesTs', time());
        return $result;
    }

    /**
     * Owner-scoped bookings. Owner → /bookings first (server-scoped by host);
     * admin → /admin/bookings first. Caches in session for 60s.
     */
    private function myBookings(array $headers): array
    {
        if ($this->rawToken() === '') return [];
        $session = $this->getRequest()->getSession();
        $cached = $session->read('HostBookings');
        $ts = (int)$session->read('HostBookingsTs');
        if (is_array($cached) && !empty($cached) && $ts > time() - 60) {
            return $cached;
        }

        $first = $this->isAdminUser() ? '/admin/bookings' : '/bookings';
        $second = $this->isAdminUser() ? '/bookings' : '/admin/bookings';
        $this->releaseSession();
        $res = $this->apiClient->get($first, [], $headers);
        if ($res === null) {
            $this->markBackend($res);
            return is_array($cached) ? $cached : [];
        }
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->apiClient->get($second, [], $headers);
        }
        $this->markBackend($res);
        $list = $res['data'] ?? (isset($res[0]) ? $res : []);
        $result = is_array($list) ? array_values($list) : [];
        $session->write('HostBookings', $result);
        $session->write('HostBookingsTs', time());
        return $result;
    }

    /**
     * Session-cached profile (120s TTL) — backend /user/personal-details
     * takes ~2.3s, and sidebar/topbar only need it for display.
     * Backend stays source of truth; refresh on profile update.
     * Releases the session lock before/after slow I/O so parallel
     * portal requests (prefetch) don't serialize on the session file.
     */
    private function cachedProfile(): array
    {
        if ($this->rawToken() === '') return [];
        $session = $this->getRequest()->getSession();
        $cached = $session->read('UserProfile');
        $ts = (int)$session->read('UserProfileTs');
        if (is_array($cached) && !empty($cached) && $ts > time() - 120) {
            $session->close();
            return $cached;
        }
        $session->close();
        $fresh = $this->authService->getPersonalDetails($this->rawToken());
        if (!empty($fresh) && !empty($fresh['id'])) { // id required: public personal-details returns a demo profile (null id) for bad tokens
            $session->write('UserProfile', $fresh);
            $session->write('UserProfileTs', time());
            $session->close();
            return $fresh;
        }
        return is_array($cached) && !empty($cached) ? $cached : $fresh;
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // No login wall: portal always renders. Without a session the pages
        // show empty states + a sign-in banner (portal.php); backend calls
        // simply return nothing without a token. Nothing here may redirect.
    }

    public function dashboard()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $properties = $this->myProperties($headers);

        $bookings = $this->myBookings($headers);

        $stats = [
            'properties' => count($properties),
            'bookings' => count($bookings),
            'revenue' => array_sum(array_map(fn($b) => (float)($b['total_price'] ?? 0), $bookings)),
        ];
        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'properties', 'bookings', 'stats', 'backendError'));
        return $this->render('/Pages/host-dashboard');
    }

    /**
     * Cache-bust bridge for direct-API saves: the browser writes straight to
     * the backend, then hits this (fast, no backend call) to drop stale
     * session caches (profile, onboarding draft) before landing on the list
     * page. `go` is restricted to local paths. quiet=1 returns 204 for
     * background busts after optimistic saves.
     */
    public function cacheBust()
    {
        $q = $this->getRequest()->getQueryParams();
        $scope = array_values(array_filter(array_map('trim', explode(',', (string)($q['scope'] ?? '')))));
        if (in_array('profile', $scope, true)) {
            $sess = $this->getRequest()->getSession();
            $sess->delete('UserProfile');
            $sess->delete('UserProfileTs');
        }
        if (!empty($q['draft'])) {
            $this->getRequest()->getSession()->delete('OnboardDraft');
        }
        if (!empty($q['quiet'])) {
            $this->autoRender = false;
            return $this->response->withStatus(204);
        }
        $go = (string)($q['go'] ?? '/host/dashboard');
        if (!str_starts_with($go, '/') || str_starts_with($go, '//')) {
            $go = '/host/dashboard';
        }
        return $this->redirect($go);
    }

    public function listings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $properties = $this->myProperties($headers);
        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'properties', 'backendError'));
        return $this->render('/Pages/host-listings');
    }

    /**
     * Map-picker vars for location steps: token/style resolved server-side
     * (2s fail-fast, cached 5min) + an instant static preview image, so the
     * step-2 map paints immediately without waiting on extra round trips.
     */
    private function mapPickVars(float $lat, float $lng): array
    {
        $mapToken = '';
        $mapStyle = 'mapbox://styles/mapbox/streets-v12';
        $mapPreviewUrl = '';
        try {
            $maps = new \App\Service\MapService($this->apiClient);
            $cfg = $maps->getMapConfig(2);
            if (!empty($cfg['token']) && is_string($cfg['token']) && str_starts_with($cfg['token'], 'pk.')) {
                $mapToken = $cfg['token'];
            }
            if (!empty($cfg['style']) && is_string($cfg['style'])) {
                $mapStyle = $cfg['style'];
            }
            $mapPreviewUrl = $maps->getStaticMapUrl($lat, $lng, 12, 640, 340);
        } catch (\Throwable $e) {
        }
        return compact('mapToken', 'mapStyle', 'mapPreviewUrl');
    }

    /**
     * Build property payload with backend-required defaults (area defaults to city).
     */
    private function buildPropertyPayload(array $data): array
    {
        $city = trim((string)($data['city'] ?? 'Dar es Salaam'));
        if ($city === '') $city = 'Dar es Salaam';
        $area = trim((string)($data['area'] ?? ''));
        if ($area === '') $area = $city;
        return [
            'name' => trim((string)($data['name'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
            'address' => trim((string)($data['address'] ?? '')),
            'city' => $city,
            'area' => $area,
            'price_per_night' => (float)($data['price_per_night'] ?? 0),
            'latitude' => is_numeric($data['latitude'] ?? null) ? (float)$data['latitude'] : -6.7924,
            'longitude' => is_numeric($data['longitude'] ?? null) ? (float)$data['longitude'] : 39.2083,
            'image_url' => trim((string)($data['image_url'] ?? '')),
        ];
    }

    private function propertyErrorMessage(?array $res): string
    {
        if (empty($res)) return 'Service unavailable. Please try again.';
        $msg = trim((string)($res['message'] ?? 'Could not create listing.'));
        if (!empty($res['errors']) && is_array($res['errors'])) {
            $flat = [];
            foreach ($res['errors'] as $fieldErrors) {
                foreach ((array)$fieldErrors as $e) $flat[] = $e;
            }
            if (!empty($flat)) $msg .= ' ' . implode(' ', array_slice($flat, 0, 3));
        }
        return $msg;
    }

    private function submitLodgeVerification(int $propertyId, array $headers): void
    {
        try {
            $this->apiClient->post('/verification/lodge/' . $propertyId, [], $headers);
        } catch (\Throwable $e) {
            // Non-fatal: property is created Active by default; admin can still review
        }
    }

    public function create()
    {
        $headers = $this->hostHeaders();
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $payload = $this->buildPropertyPayload($data);
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Name and price are required.'));
            } else {
                $res = $this->apiClient->post('/properties', $payload, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/host/listings/add')) return $bounce;
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($this->propertyErrorMessage($res)));
                } else {
                    $pid = (int)(($res['id'] ?? $res['data']['id'] ?? 0));
                    $this->clearHostPropertiesCache();
                    if ($pid > 0) $this->submitLodgeVerification($pid, $headers);
                    $this->Flash->success(__('Property created and submitted for verification. Add rooms next.'));
                    return $this->redirect(['action' => 'rooms']);
                }
            }
        }
        $userProfile = $this->cachedProfile();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/host-listing-form');
    }

    public function bookings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $bookings = $this->myBookings($headers);
        $this->set(compact('userProfile', 'bookings'));
        return $this->render('/Pages/host-bookings');
    }

    public function calendar(?int $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $property = null;
        $rooms = [];
        if ($id) {
            // Fast path: owner-scoped list (mine=1, has host_id) instead of
            // /properties/{id} show (~7s). Falls back to show if missing.
            $property = null;
            $list = $this->myProperties($headers);
            foreach ($list as $p) {
                if ((int)($p['id'] ?? 0) === (int)$id) { $property = $p; break; }
            }
            if ($property === null) {
                $res = $this->apiClient->get('/properties/' . $id, [], $headers);
                $property = $res['data'] ?? $res;
            }
            if (empty($property) || !empty($property['_status'])) {
                $this->Flash->error(__('Property not found.'));
                return $this->redirect(['action' => 'listings']);
            }
            // Ownership guard: non-admins only manage their own lodges
            if (!$this->isAdminUser()) {
                $oid = $this->ownerId();
                $hid = (int)($property['host_id'] ?? $property['host']['id'] ?? 0);
                if ($hid > 0 && $oid > 0 && $hid !== $oid) {
                    $this->Flash->error(__('Property not found.'));
                    return $this->redirect(['action' => 'listings']);
                }
            }
            $rRes = $this->apiClient->get('/properties/' . $id . '/rooms', [], $headers);
            $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
            if (!is_array($rooms) && !empty($property['rooms'])) $rooms = $property['rooms'];
            if (!is_array($rooms)) $rooms = [];
        } else {
            // no id → redirect to listings picker
            return $this->redirect(['action' => 'listings']);
        }
        // Handle inline room price/status update (mobile calendar_pricing_editor.dart parity)
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $roomId = (int)($data['room_id'] ?? 0);
            if ($roomId > 0) {
                $payload = [];
                if (isset($data['price'])) $payload['price'] = (float)$data['price'];
                if (isset($data['customer_price'])) $payload['customer_price'] = (float)$data['customer_price'];
                if (isset($data['status'])) $payload['status'] = trim((string)$data['status']);
                if (!empty($payload)) {
                    $upRes = $this->apiClient->put('/rooms/' . $roomId, $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($upRes, '/host/calendar/' . $id)) return $bounce;
                    if (!empty($upRes['_status']) && (int)$upRes['_status'] >= 400) {
                        $this->Flash->error(__($upRes['message'] ?? 'Could not update room.'));
                    } else {
                        $this->Flash->success(__('Room updated.'));
                        return $this->redirect(['action' => 'calendar', $id]);
                    }
                }
            }
        }
        $this->set(compact('userProfile', 'property', 'rooms'));
        return $this->render('/Pages/host-calendar');
    }

    public function earnings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        // Finance endpoints take ~7-8s each — 90s session cache so sidebar
        // navigation stays fast; backend stays source of truth on refresh.
        $session = $this->getRequest()->getSession();
        $finance = $session->read('FinanceCache');
        $payouts = $session->read('PayoutsCache');
        $fts = (int)$session->read('FinanceCacheTs');
        if (!is_array($finance) || !is_array($payouts) || $fts < time() - 90) {
            // Try finance overview, fallback to admin dashboardStats (admin_owner_portal/chart-flot.php parity)
            $res = $this->apiClient->get('/finance/overview', [], $headers);
            if (empty($res) || !empty($res['_status'])) {
                $res = $this->apiClient->get('/admin/dashboard-stats', [], $headers);
            }
            if (empty($res) || !empty($res['_status'])) {
                $res = $this->apiClient->get('/admin/owners/financial-summary', [], $headers);
            }
            $finance = $res['data'] ?? $res;
            if (!is_array($finance)) $finance = [];
            // payouts ledger fallback for mobile financial_reports.dart
            $pRes = $this->apiClient->get('/payouts', [], $headers);
            $payouts = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
            if (!is_array($payouts)) $payouts = [];
            $session->write('FinanceCache', $finance);
            $session->write('PayoutsCache', $payouts);
            $session->write('FinanceCacheTs', time());
        }
        $this->set(compact('userProfile', 'finance', 'payouts'));
        return $this->render('/Pages/host-earnings');
    }

    // ---- Working-only additions mirroring admin_owner_portal ----

    public function rooms()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $search = trim((string)$this->getRequest()->getQuery('search', ''));
        $status = trim((string)$this->getRequest()->getQuery('status', ''));

        // Fetch properties for owner filter + dropdown (owner-scoped)
        $properties = $this->myProperties($headers);

        // Fetch all rooms
        $rRes = $this->apiClient->get('/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];
        // Fallback per-property only if /rooms empty — capped at 3 to avoid
        // N+1 fan-out (~1.9s each) blocking sidebar navigation.
        // Skipped when /rooms itself failed transport (null): the backend is
        // down, so per-property calls would just burn 3 more timeouts.
        if (empty($rooms) && $rRes !== null && !empty($properties)) {
            $agg = [];
            foreach (array_slice($properties, 0, 3) as $p) {
                $pid = $p['id'] ?? null;
                if (!$pid) continue;
                $rr = $this->apiClient->get('/properties/' . $pid . '/rooms', [], $headers);
                $list = $rr['data'] ?? (isset($rr[0]) ? $rr : []);
                if (is_array($list)) $agg = array_merge($agg, $list);
            }
            if (!empty($agg)) $rooms = $agg;
        }

        // Search filter (client-side parity room-list.php:search)
        if ($search !== '') {
            $low = strtolower($search);
            $rooms = array_values(array_filter($rooms, function ($r) use ($low, $properties) {
                $fields = [
                    strtolower((string)($r['room_number'] ?? $r['name'] ?? '')),
                    strtolower((string)($r['room_type'] ?? $r['type'] ?? '')),
                    strtolower((string)($r['floor'] ?? '')),
                    strtolower((string)($r['status'] ?? '')),
                ];
                // enrich with property name/city
                $pid = $r['property_id'] ?? $r['property']['id'] ?? null;
                if ($pid) {
                    foreach ($properties as $p) {
                        if ((int)$p['id'] === (int)$pid) {
                            $fields[] = strtolower((string)($p['name'] ?? ''));
                            $fields[] = strtolower((string)($p['city'] ?? ''));
                            break;
                        }
                    }
                }
                foreach ($fields as $f) if (str_contains($f, $low)) return true;
                return false;
            }));
        }
        if ($status !== '' && $status !== 'all') {
            $rooms = array_values(array_filter($rooms, fn($r) => strtolower((string)($r['status'] ?? '')) === strtolower($status)));
        }

        $this->set(compact('userProfile', 'properties', 'rooms', 'search', 'status'));
        return $this->render('/Pages/host-rooms');
    }

    /**
     * Normalize amenity input: form may send a single comma-joined string
     * ("Wifi, AC, TV") or an array — backend expects a clean string array.
     */
    private function amenityList(mixed $raw): array
    {
        $out = [];
        foreach ((array)$raw as $item) {
            foreach (explode(',', (string)$item) as $part) {
                $part = trim($part);
                if ($part !== '') $out[] = $part;
            }
        }
        return array_values(array_unique($out));
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

    public function addRoom()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $properties = $this->myProperties($headers);

        // Preselect a property arriving from onboarding (?property_id=)
        $wantPid = (int)$this->getRequest()->getQuery('property_id', 0);
        if ($wantPid > 0) {
            usort($properties, fn($a, $b) => ((int)($b['id'] ?? 0) === $wantPid) <=> ((int)($a['id'] ?? 0) === $wantPid));
        }

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $propertyId = (int)($data['property_id'] ?? $this->getRequest()->getQuery('property_id', 0));
            if ($propertyId <= 0 && !empty($properties)) $propertyId = (int)($properties[0]['id'] ?? 0);
            if ($propertyId <= 0) {
                $this->Flash->error(__('No property available. Create a property first.'));
            } else {
                $payload = [
                    'room_number' => trim((string)($data['room_number'] ?? '')),
                    'room_type' => trim((string)($data['room_type'] ?? 'Standard')),
                    'price' => (float)($data['price'] ?? 0),
                    'capacity' => (int)($data['capacity'] ?? 1),
                    'max_adults' => (int)($data['max_adults'] ?? $data['capacity'] ?? 2),
                    'max_children' => (int)($data['max_children'] ?? 0),
                    'bed_configuration' => trim((string)($data['bed_configuration'] ?? '')),
                    'number_of_beds' => (int)($data['number_of_beds'] ?? 1),
                    'floor' => trim((string)($data['floor'] ?? '')),
                    'room_size' => trim((string)($data['room_size'] ?? '')),
                    'status' => trim((string)($data['status'] ?? 'available')),
                    'description' => trim((string)($data['description'] ?? '')),
                    'amenities' => $this->amenityList($data['amenities'] ?? []),
                    'photos' => array_values(array_filter(array_map('trim', (array)($data['photos'] ?? [])))),
                ];
                if ($payload['room_number'] === '' || $payload['price'] <= 0) {
                    $this->Flash->error(__('Room number and price are required.'));
                } else {
                    $res = $this->apiClient->post('/properties/' . $propertyId . '/rooms', $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($res, '/host/rooms/add')) return $bounce;
                    if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                        $this->Flash->error(__($res['message'] ?? 'Could not create room.'));
                    } else {
                        $this->Flash->success(__('Room created.'));
                        return $this->redirect(['action' => 'rooms']);
                    }
                }
            }
        }

        $isEdit = false;
        $room = null;
        $this->set(compact('userProfile', 'properties', 'room', 'isEdit'));
        return $this->render('/Pages/host-room-form');
    }

    public function editRoom(?string $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $roomId = (int)$id;
        if ($roomId <= 0) return $this->redirect(['action' => 'rooms']);

        // Fetch room
        $rRes = $this->apiClient->get('/rooms/' . $roomId, [], $headers);
        $room = $rRes['data'] ?? $rRes;
        if (empty($room) || !empty($room['_status']) || empty($room['id'])) {
            $this->Flash->error(__('Room not found.'));
            return $this->redirect(['action' => 'rooms']);
        }

        $properties = $this->myProperties($headers);

        if ($this->getRequest()->is(['post','put','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $payload = [];
            foreach (['room_number','room_type','type','price','capacity','max_adults','max_children','bed_configuration','number_of_beds','floor','room_size','status','description'] as $k) {
                if (isset($data[$k])) {
                    if (in_array($k, ['price','capacity','max_adults','max_children','number_of_beds'])) {
                        $payload[$k === 'type' ? 'room_type' : $k] = is_numeric($data[$k]) ? (float)$data[$k] : trim((string)$data[$k]);
                    } else {
                        $payload[$k === 'type' ? 'room_type' : $k] = trim((string)$data[$k]);
                    }
                }
            }
            if (isset($data['amenities'])) $payload['amenities'] = $this->amenityList($data['amenities']);
            if (isset($data['photos'])) $payload['photos'] = array_values(array_filter(array_map('trim', (array)$data['photos'])));

            $res = $this->apiClient->put('/rooms/' . $roomId, $payload, $headers);
            if ($bounce = $this->bounceOnUnauth($res, '/host/rooms/' . $roomId)) return $bounce;
            if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                $this->Flash->error(__($res['message'] ?? 'Could not update room.'));
            } else {
                $this->Flash->success(__('Room updated.'));
                return $this->redirect(['action' => 'rooms']);
            }
        }

        $isEdit = true;
        $this->set(compact('userProfile', 'properties', 'room', 'isEdit'));
        return $this->render('/Pages/host-room-form');
    }

    public function editLodge(?string $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $propId = $id !== null ? (int)$id : null;

        $properties = $this->myProperties($headers);

        $property = null;
        if ($propId) {
            foreach ($properties as $p) {
                if ((int)($p['id'] ?? 0) === (int)$propId) { $property = $p; break; }
            }
            if ($property === null) {
                $r = $this->apiClient->get('/properties/' . $propId, [], $headers);
                $property = $r['data'] ?? $r;
                if (empty($property) || !empty($property['_status'])) $property = null;
            }
        }
        if (!$property && !empty($properties)) $property = $properties[0];
        if (!$property) {
            $this->Flash->error(__('No property found. Create one first.'));
            return $this->redirect(['action' => 'listings']);
        }
        // Ownership guard for explicit ids
        if ($propId && !$this->isAdminUser()) {
            $oid = $this->ownerId();
            $hid = (int)($property['host_id'] ?? $property['host']['id'] ?? 0);
            if ($hid > 0 && $oid > 0 && $hid !== $oid) {
                $this->Flash->error(__('Property not found.'));
                return $this->redirect(['action' => 'listings']);
            }
        }
        $propId = (int)($property['id'] ?? $propId);

        // Wizard step for the template (1 Basics → 2 Location → 3 Photos → 4 Review).
        $step = (int)$this->getRequest()->getQuery('step', 1);
        if ($step < 1 || $step > 4) $step = 1;

        // Aggregate room types/amenities for chips
        $rRes = $this->apiClient->get('/properties/' . $propId . '/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];

        if ($this->getRequest()->is(['post','put','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $payload = [
                'name' => trim((string)($data['name'] ?? $property['name'] ?? '')),
                'description' => trim((string)($data['description'] ?? $property['description'] ?? '')),
                'address' => trim((string)($data['address'] ?? $property['address'] ?? '')),
                'city' => trim((string)($data['city'] ?? $property['city'] ?? '')),
                'area' => trim((string)($data['area'] ?? $property['area'] ?? '')),
                'price_per_night' => (float)($data['price_per_night'] ?? $property['price_per_night'] ?? 0),
                'image_url' => trim((string)($data['image_url'] ?? $property['image_url'] ?? '')),
                'amenities' => $this->amenityList($data['amenities'] ?? $property['amenities'] ?? []),
            ];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Name and price are required.'));
            } else {
                $res = $this->apiClient->put('/properties/' . $propId, $payload, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/host/lodge/' . $propId . '/edit')) return $bounce;
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update lodge.'));
                } else {
                    $this->clearHostPropertiesCache();
                    $this->Flash->success(__('Lodge updated.'));
                    return $this->redirect(['action' => 'listings']);
                }
            }
        }

        $this->set(compact('userProfile', 'property', 'rooms', 'step'));
        if ($step === 2) {
            $pLat = is_numeric($property['latitude'] ?? $property['lat'] ?? null) ? (float)($property['latitude'] ?? $property['lat']) : -6.7924;
            $pLng = is_numeric($property['longitude'] ?? $property['lng'] ?? null) ? (float)($property['longitude'] ?? $property['lng']) : 39.2083;
            $this->set($this->mapPickVars($pLat, $pLng));
        }
        return $this->render('/Pages/host-lodge-form');
    }

    /**
     * Real multi-page onboarding wizard (?step=1..5) with session draft.
     * 1 Basics → 2 Location → 3 Photos → 4 Rooms (many, each with pictures)
     * → 5 Review & launch. Rooms belong to this lodge only.
     */
    public function onboarding()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $session = $this->getRequest()->getSession();

        $step = (int)$this->getRequest()->getQuery('step', 1);
        if ($step < 1 || $step > 5) $step = 1;
        $draft = $session->read('OnboardDraft');
        if (!is_array($draft)) $draft = [];

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $postedStep = (int)($data['wizard_step'] ?? $step);

            if ($postedStep === 1) {
                $draft['name'] = trim((string)($data['name'] ?? ''));
                $draft['type'] = trim((string)($data['type'] ?? 'Lodge'));
                $draft['description'] = trim((string)($data['description'] ?? ''));
                $draft['price_per_night'] = (float)($data['price_per_night'] ?? 0);
                if ($draft['name'] === '' || $draft['price_per_night'] <= 0) {
                    $this->Flash->error(__('Property name and nightly price are required.'));
                    $this->set(compact('userProfile', 'draft', 'step'));
                    return $this->render('/Pages/host-onboarding');
                }
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 2]]);
            }

            if ($postedStep === 2) {
                $draft['city'] = trim((string)($data['city'] ?? ''));
                $draft['area'] = trim((string)($data['area'] ?? ''));
                $draft['address'] = trim((string)($data['address'] ?? ''));
                $draft['latitude'] = is_numeric($data['latitude'] ?? null) ? (float)$data['latitude'] : -6.7924;
                $draft['longitude'] = is_numeric($data['longitude'] ?? null) ? (float)$data['longitude'] : 39.2083;
                if ($draft['city'] === '') {
                    $this->Flash->error(__('City is required — pick your location on the map.'));
                    $step = 2;
                    $this->set(compact('userProfile', 'draft', 'step'));
                    $this->set($this->mapPickVars((float)($draft['latitude'] ?? -6.7924), (float)($draft['longitude'] ?? 39.2083)));
                    return $this->render('/Pages/host-onboarding');
                }
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 3]]);
            }

            if ($postedStep === 3) {
                $draft['image_url'] = trim((string)($data['image_url'] ?? ''));
                if ($draft['image_url'] === '') {
                    $this->Flash->error(__('A cover photo is required — upload one above.'));
                    $step = 3;
                    $this->set(compact('userProfile', 'draft', 'step'));
                    return $this->render('/Pages/host-onboarding');
                }
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 4]]);
            }

            if ($postedStep === 4) {
                // Category-first rooms: each category (Deluxe…) carries shared
                // attributes + many room numbers (45, 78…). Expand to rows.
                $rooms = [];
                $rawCats = $data['cats'] ?? [];
                if (is_array($rawCats)) {
                    foreach ($rawCats as $cat) {
                        if (!is_array($cat)) continue;
                        $price = (float)($cat['price'] ?? 0);
                        if ($price <= 0) continue;
                        $nums = [];
                        foreach ((array)($cat['numbers'] ?? []) as $n) {
                            $n = trim((string)$n);
                            if ($n !== '' && !in_array($n, $nums, true)) $nums[] = $n;
                        }
                        if (empty($nums)) continue;
                        $photos = [];
                        foreach ((array)($cat['photos'] ?? []) as $ph) {
                            $ph = trim((string)$ph);
                            if ($ph !== '') $photos[] = $ph;
                        }
                        $photos = array_values($photos);
                        $shared = [
                            'room_type' => trim((string)($cat['room_type'] ?? 'Standard')),
                            'price' => $price,
                            'capacity' => max(1, (int)($cat['capacity'] ?? 2)),
                            'max_adults' => max(1, (int)($cat['max_adults'] ?? $cat['capacity'] ?? 2)),
                            'max_children' => max(0, (int)($cat['max_children'] ?? 0)),
                            'bed_configuration' => trim((string)($cat['bed_configuration'] ?? '')),
                            'status' => trim((string)($cat['status'] ?? 'available')),
                            'amenities' => $this->amenityList($cat['amenities'] ?? []),
                            'photos' => $photos,
                        ];
                        foreach ($nums as $num) {
                            $rooms[] = array_merge(['room_number' => $num], $shared);
                        }
                    }
                }
                $draft['rooms'] = $rooms;
                // Keep category view for re-render: stash raw groups too
                $draft['roomCats'] = is_array($rawCats) ? array_values($rawCats) : [];
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 5]]);
            }

            // Step 5 · Review & launch: lodge + all its rooms, then verify
            $draft['amenities'] = $this->amenityList($data['amenities'] ?? ($draft['amenities'] ?? []));
            $payload = $this->buildPropertyPayload($draft);
            $payload['amenities'] = $draft['amenities'];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Basics are incomplete — back to step 1.'));
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 1]]);
            }
            $res = $this->apiClient->post('/properties', $payload, $headers);
            // Dead token → re-login with progress intact (draft stays in session)
            if ($bounce = $this->bounceOnUnauth($res, '/host/onboarding?step=5')) {
                $session->write('OnboardDraft', $draft);
                return $bounce;
            }
            if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                $this->Flash->error(__($this->propertyErrorMessage($res)));
                $step = 5;
                $session->write('OnboardDraft', $draft);
                $this->set(compact('userProfile', 'draft', 'step'));
                return $this->render('/Pages/host-onboarding');
            }
            $pid = (int)(($res['id'] ?? $res['data']['id'] ?? 0));
            $roomFails = [];
            $roomUnauth = false;
            
            if ($pid > 0) {
                $multiRequests = [];
                foreach ((array)($draft['rooms'] ?? []) as $i => $rm) {
                    $multiRequests[$i] = ['endpoint' => '/properties/' . $pid . '/rooms', 'data' => $rm];
                }
                if (!empty($multiRequests)) {
                    $multiResults = $this->apiClient->postMulti($multiRequests, $headers);
                    foreach ($multiResults as $i => $rRes) {
                        if (is_array($rRes) && (int)($rRes['_status'] ?? 0) === 401) { $roomUnauth = true; }
                        if (empty($rRes) || (!empty($rRes['_status']) && (int)$rRes['_status'] >= 400)) {
                            // Extract original room number to report failure
                            $rm = $multiRequests[$i]['data'];
                            $roomFails[] = (string)($rm['room_number'] ?? '?');
                        }
                    }
                }
            }
            
            if ($roomUnauth) {
                // Lodge exists — rooms can be added after re-login; keep no stale draft
                $session->delete('OnboardDraft');
                $this->Flash->error(__('Session expired — please sign in again to add rooms.'));
                return $this->redirect('/login?redirect=' . urlencode('/host/rooms/add?property_id=' . $pid));
            }
            if ($pid > 0) $this->submitLodgeVerification($pid, $headers);
            $this->clearHostPropertiesCache();
            $session->delete('OnboardDraft');
            $nRooms = count((array)($draft['rooms'] ?? [])) - count($roomFails);
            if (!empty($roomFails)) {
                $this->Flash->error(__('Rooms not created ({0}) — numbers may already exist. Add them under Rooms.', implode(', ', $roomFails)));
            }
            $this->Flash->success(__('Property onboarded with {0} room(s) and submitted for verification.', $nRooms));
            return $this->redirect(['action' => 'rooms']);
        }

        $this->set(compact('userProfile', 'draft', 'step'));
        if ($step === 2) {
            $this->set($this->mapPickVars((float)($draft['latitude'] ?? -6.7924), (float)($draft['longitude'] ?? 39.2083)));
        }
        return $this->render('/Pages/host-onboarding');
    }

    public function profile()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $me = $userProfile;

        // Try /me for fuller data
        $meRes = $this->apiClient->get('/me', [], $headers);
        if (!empty($meRes) && empty($meRes['_status'])) {
            $meCand = $meRes['data'] ?? $meRes['user'] ?? $meRes;
            if (is_array($meCand) && !empty($meCand['email'])) $me = array_merge($me, $meCand);
        }

        if ($this->getRequest()->is(['post','put','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $payload = array_filter([
                'name' => trim((string)($data['name'] ?? '')),
                'email' => trim((string)($data['email'] ?? '')),
                'phone' => trim((string)($data['phone'] ?? $data['phone_number'] ?? '')),
                'phone_number' => trim((string)($data['phone'] ?? $data['phone_number'] ?? '')),
                'address' => trim((string)($data['address'] ?? '')),
                'bio' => trim((string)($data['bio'] ?? '')),
                'profile_photo_url' => trim((string)($data['profile_photo_url'] ?? '')),
            ], fn($v) => $v !== '');
            if (!empty($payload)) {
                // Backend source of truth: PATCH /profile (auth:sanctum)
                $res = $this->apiClient->patch('/profile', $payload, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/host/profile')) return $bounce;
                if ($res === null) {
                    $this->Flash->error(__('Could not update profile — service unavailable.'));
                } elseif (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update profile.'));
                } else {
                    $this->Flash->success(__('Profile updated.'));
                    // Invalidate session profile so next read is fresh from backend
                    $this->getRequest()->getSession()->delete('UserProfile');
                    $this->getRequest()->getSession()->delete('UserProfileTs');
                    $userProfile = $this->cachedProfile();
                    $me = $userProfile;
                }
            }
        }

        // Stats for header (properties/rooms) — owner-scoped
        $properties = $this->myProperties($headers);
        $rRes = $this->apiClient->get('/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];

        $stats = ['properties' => count($properties), 'rooms' => count($rooms)];
        $this->set(compact('userProfile', 'me', 'stats'));
        return $this->render('/Pages/host-profile');
    }
}
