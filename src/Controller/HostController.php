<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
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
            // Per-booking stored values, so a non-standard commission rate
            // reconciles with the payout ledger instead of showing zero.
            'platform_fee' => array_sum(array_map(fn($b) => (float)($b['platform_fee'] ?? 0), $bookings)),
            'owner_earnings' => array_sum(array_map(fn($b) => (float)($b['owner_payout'] ?? $b['owner_earnings'] ?? 0), $bookings)),
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
            // Step-1 type (Lodge/Hotel/Apartment) was previously dropped here,
            // so every property saved without a type. Map to the backend enum.
            'property_type' => self::mapPropertyType($data['type'] ?? $data['property_type'] ?? ''),
            'price_per_night' => (float)($data['price_per_night'] ?? 0),
            'latitude' => is_numeric($data['latitude'] ?? null) ? (float)$data['latitude'] : -6.7924,
            'longitude' => is_numeric($data['longitude'] ?? null) ? (float)$data['longitude'] : 39.2083,
            'image_url' => trim((string)($data['image_url'] ?? '')),
            // The API accepts an amenities array; amenityList() also accepts a
            // comma-separated string, so the free-text field works directly.
            'amenities' => $this->amenityList($data['property_amenities'] ?? []),
        ];
    }

    /**
     * Map the onboarding step-1 type to the backend property_type enum
     * (Hotel, Resort, Apartment, Safari Lodge, Villa).
     */
    public static function mapPropertyType(mixed $raw): string
    {
        $t = strtolower(trim((string)$raw));
        return match (true) {
            $t === 'hotel' => 'Hotel',
            $t === 'resort' => 'Resort',
            $t === 'apartment' => 'Apartment',
            $t === 'villa' => 'Villa',
            $t === 'safari lodge', $t === 'lodge', $t === 'safari' => 'Safari Lodge',
            default => 'Safari Lodge',
        };
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
        return $this->redirect(['action' => 'onboarding'], 301);
    }

    /**
     * Pull the repeatable room rows out of the wizard submission.
     *
     * Rows are named rooms[0][room_number] etc. Completely blank rows are
     * dropped so the host is not forced to fill every row they added.
     *
     * @return array<int, array<string, mixed>>
     */
    private function collectWizardRooms(array $data): array
    {
        $rows = $data['rooms'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $roomNumber = trim((string)($row['room_number'] ?? ''));
            $price = (float)($row['price'] ?? 0);

            // A row with neither an identifier nor a rate is an unused slot.
            if ($roomNumber === '' && $price <= 0.0) {
                continue;
            }

            $out[] = [
                'room_number'        => $roomNumber,
                'room_type'          => trim((string)($row['room_type'] ?? '')) ?: 'Standard',
                'price'              => $price,
                'capacity'           => max(1, (int)($row['capacity'] ?? 2)),
                'max_adults'         => max(1, (int)($row['max_adults'] ?? 0)) ?: null,
                'max_children'       => max(0, (int)($row['max_children'] ?? 0)),
                'number_of_beds'     => max(1, (int)($row['number_of_beds'] ?? 1)),
                'bed_configuration'  => trim((string)($row['bed_configuration'] ?? '')),
                'room_size'          => trim((string)($row['room_size'] ?? '')),
                'floor'              => trim((string)($row['floor'] ?? '')),
                'status'             => trim((string)($row['status'] ?? 'available')) ?: 'available',
                'description'        => trim((string)($row['description'] ?? '')),
                'amenities'          => $this->amenityList($row['amenities'] ?? []),
            ];
        }

        return $out;
    }

    /**
     * Create rooms one at a time so a single bad row cannot lose the others.
     *
     * @param array<int, array<string, mixed>> $rooms
     * @return array{0:int,1:array<int,string>,2:bool} [createdCount, errors, authFailed]
     */
    private function createRooms(int $propertyId, array $rooms, array $headers): array
    {
        $created = 0;
        $errors = [];
        $authFailed = false;

        foreach ($rooms as $index => $room) {
            if ($room['room_number'] === '' || $room['price'] <= 0.0) {
                $label = $room['room_number'] !== '' ? $room['room_number'] : ('Room ' . ($index + 1));
                $errors[$index] = $label . ': room number and price are both required.';
                continue;
            }

            $payload = array_filter([
                'room_number'       => $room['room_number'],
                'room_type'         => $room['room_type'],
                'price'             => $room['price'],
                'capacity'          => $room['capacity'],
                'max_adults'        => $room['max_adults'],
                'max_children'      => $room['max_children'],
                'number_of_beds'    => $room['number_of_beds'],
                'bed_configuration' => $room['bed_configuration'],
                'room_size'         => $room['room_size'],
                'floor'             => $room['floor'],
                'status'            => $room['status'],
                'description'       => $room['description'],
                'amenities'         => $room['amenities'],
            ], static fn($v) => $v !== null && $v !== '');

            $res = $this->apiClient->post('/properties/' . $propertyId . '/rooms', $payload, $headers);

            // A dead session would otherwise surface as N identical per-room
            // errors. Flag it so the caller can bounce to sign-in instead.
            $status = (int)($res['_status'] ?? 0);
            if ($res === null || $status === 401) {
                $authFailed = true;
                return [$created, $errors, true];
            }

            if ($status >= 400) {
                $errors[$index] = $room['room_number'] . ': ' . $this->propertyErrorMessage($res);
                continue;
            }

            $created++;
        }

        return [$created, $errors, false];
    }

    public function bookings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $bookings = $this->myBookings($headers);
        $this->set(compact('userProfile', 'bookings'));
        return $this->render('/Pages/host-bookings');
    }

    /**
     * Professional arrival / departure from the host portal.
     * POST-only with CSRF + the host gate in beforeFilter; the backend
     * enforces ownership, paid-before-check-in and state order.
     */
    public function checkIn(string $id)
    {
        return $this->moveStay($id, 'check-in');
    }

    public function checkOut(string $id)
    {
        return $this->moveStay($id, 'check-out');
    }

    private function moveStay(string $id, string $move): ?\Cake\Http\Response
    {
        if (!$this->getRequest()->is('post')) {
            return $this->redirect(['action' => 'bookings']);
        }
        $id = trim($id);
        if ($id === '') {
            $this->Flash->error(__('Booking not specified.'));
            return $this->redirect(['action' => 'bookings']);
        }
        $res = $this->apiClient->post('/bookings/' . rawurlencode($id) . '/' . $move, [], $this->hostHeaders());
        if (is_array($res) && empty($res['_status']) && ($res['status'] ?? '') === 'success') {
            $this->Flash->success(__((string)($res['message'] ?? 'Done.')));
        } else {
            $this->Flash->error(__((string)($res['message'] ?? 'Could not update this booking.')));
        }
        // Refresh cached lists so the new state shows immediately.
        try {
            $session = $this->getRequest()->getSession();
            $session->delete('HostBookings');
            $session->delete('HostBookingsTs');
        } catch (\Throwable $e) {
        }
        return $this->redirect(['action' => 'bookings']);
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
        // (view vars are set below, after the availability map is built)

        // Real availability: map booked date ranges per room for the current
        // month from actual bookings. Cancelled/refunded stays do not block.
        $bookedDays = [];
        try {
            $allBookings = $this->myBookings($headers);
            $roomIds = array_map(fn($rm) => (int)($rm['id'] ?? 0), $rooms);
            $monthStart = new \DateTimeImmutable('first day of this month');
            $monthEnd = new \DateTimeImmutable('last day of this month');
            foreach ($allBookings as $b) {
                if (!is_array($b)) continue;
                $rid = (int)($b['room_id'] ?? ($b['room']['id'] ?? 0));
                if ($rid <= 0 || !in_array($rid, $roomIds, true)) continue;
                $bst = strtolower((string)($b['status'] ?? ''));
                $pst = strtolower((string)($b['payment_status'] ?? ''));
                if (in_array($bst, ['cancelled', 'canceled', 'refunded'], true)) continue;
                if ($pst !== '' && !in_array($pst, ['paid', 'pending', 'confirmed'], true)) continue;
                try {
                    $ci = new \DateTimeImmutable((string)($b['check_in'] ?? ''));
                    $co = new \DateTimeImmutable((string)($b['check_out'] ?? ''));
                } catch (\Throwable $e) {
                    continue;
                }
                if ($co <= $ci) continue;
                // Nights occupied are [check_in, check_out) — checkout day is free.
                $day = $ci > $monthStart ? $ci : $monthStart;
                $last = $co < $monthEnd->modify('+1 day') ? $co : $monthEnd->modify('+1 day');
                while ($day < $last) {
                    if ($day >= $monthStart) {
                        $bookedDays[$rid][$day->format('Y-m-d')] = true;
                    }
                    $day = $day->modify('+1 day');
                }
            }
        } catch (\Throwable $e) {
            $bookedDays = [];
        }

        $this->set(compact('userProfile', 'property', 'rooms', 'bookedDays'));
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
            // Drop the session lock: the 3 calls below are the slowest in
            // the portal (~7-8s each per legacy notes) and must not block
            // parallel prefetch/navigation on the session file.
            $this->releaseSession();
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
        // Authoritative payout balance straight from the backend.
        // GET /payouts/summary is the same calculation POST /payouts/request
        // validates against, so the amount a host can request is guaranteed to
        // match what the backend will accept.
        $sumRes = $this->apiClient->get('/payouts/summary', [], $headers);
        $payoutSummary = $sumRes['data'] ?? $sumRes;
        if (!is_array($payoutSummary)) $payoutSummary = [];

        $this->set(compact('userProfile', 'finance', 'payouts', 'payoutSummary'));
        return $this->render('/Pages/host-earnings');
    }

    /**
     * POST /payouts/request — host asks to be paid out.
     */
    public function requestPayout(): Response
    {
        if (!$this->request->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $token = $this->rawToken();
        if ($token === '') {
            throw new UnauthorizedException(__('Sign in to request a payout.'));
        }

        $data = (array)$this->request->getData();
        $payload = [
            'amount'          => $data['amount'] ?? null,
            'payment_method'  => $data['payment_method'] ?? null,
            'account_details' => $data['account_details'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ];

        $res = $this->apiClient->post('/payouts/request', $payload, [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ]);

        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            $this->Flash->error($res['message'] ?? __('Payout request failed.'));

            return $this->response->withStatus((int)$res['_status'])->withStringBody(json_encode([
                'status'  => 'error',
                'message' => $res['message'] ?? __('Payout request failed.'),
            ]));
        }

        // Balance moved, so the cached finance figures are stale.
        $session = $this->getRequest()->getSession();
        $session->delete('FinanceCacheTs');
        $session->delete('PayoutsCache');

        $this->Flash->success(__('Payout requested. Our team will process it shortly.'));

        if ($this->request->is('json')) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'status'  => 'success',
                'message' => __('Payout requested.'),
                'payout'  => $res['payout'] ?? null,
            ]));
        }

        return $this->redirect(['controller' => 'Host', 'action' => 'earnings']);
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
            $pid = (int)($res['id'] ?? $res['data']['id'] ?? $res['property']['id'] ?? $res['data']['property']['id'] ?? 0);
            if ($pid <= 0) {
                // Backend said OK but returned no id — never claim success.
                try {
                    \Cake\Log\Log::error(sprintf('[Host onboarding] POST /properties ok but no id: %s', json_encode($res)));
                } catch (\Throwable $e) {
                }
                $this->Flash->error(__($this->propertyErrorMessage($res) ?: 'Could not create listing. Please try again.'));
                $step = 5;
                $session->write('OnboardDraft', $draft);
                $this->set(compact('userProfile', 'draft', 'step'));
                return $this->render('/Pages/host-onboarding');
            }
            $roomFails = [];
            $roomFailMsg = '';
            $roomUnauth = false;

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
                        if ($roomFailMsg === '') {
                            $roomFailMsg = is_array($rRes) ? (string)($rRes['message'] ?? '') : 'room service unavailable';
                            try {
                                \Cake\Log\Log::error(sprintf('[Host onboarding] POST /properties/%d/rooms failed for room %s: %s', $pid, (string)($rm['room_number'] ?? '?'), json_encode($rRes)));
                            } catch (\Throwable $e) {
                            }
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
            $this->submitLodgeVerification($pid, $headers);
            $this->clearHostPropertiesCache();
            $session->delete('OnboardDraft');
            $nRooms = count((array)($draft['rooms'] ?? [])) - count($roomFails);
            if (!empty($roomFails)) {
                $detail = $roomFailMsg !== '' ? ' (' . mb_substr($roomFailMsg, 0, 120) . ')' : ' — numbers may already exist';
                $this->Flash->error(__('Rooms not created ({0}){1}. Add them under Rooms.', implode(', ', $roomFails), $detail));
            }
            if ($nRooms > 0) {
                $this->Flash->success(__('Property onboarded with {0} room(s) and submitted for verification.', $nRooms));
            } else {
                $this->Flash->success(__('Property created — add rooms under Rooms. It was submitted for verification.'));
            }
            return $this->redirect(['action' => 'rooms']);
        }

        $this->set(compact('userProfile', 'draft', 'step'));
        if ($step === 2) {
            $this->set($this->mapPickVars((float)($draft['latitude'] ?? -6.7924), (float)($draft['longitude'] ?? 39.2083)));
        }
        return $this->render('/Pages/host-onboarding');
    }

    /**
     * Upload the submitted avatar via POST /profile/photo and return its URL.
     * Returns null when nothing was chosen or the upload failed, so the rest
     * of the profile save still proceeds.
     */
    private function uploadAvatar(array $headers): ?string
    {
        $file = $this->getRequest()->getUploadedFile('avatarFile');
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            $this->Flash->error(__('Profile photo must be 5 MB or smaller.'));
            return null;
        }

        $tmp = $file->getStream()->getMetadata('uri');
        if (!is_string($tmp) || !is_readable($tmp)) {
            return null;
        }

        $res = $this->apiClient->uploadFile(
            '/profile/photo',
            'photo',
            $tmp,
            $file->getClientFilename(),
            $file->getClientMediaType() ?: 'image/jpeg',
            $headers
        );

        if (empty($res) || !empty($res['_status']) || empty($res['photo_url'])) {
            $this->Flash->error(__('Could not upload your photo — please try a different image.'));
            return null;
        }

        return (string)$res['photo_url'];
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
                // Only a server-issued URL is ever accepted. The form used to
                // base64-encode the whole image into this field and post it as
                // if it were a URL, storing a multi-megabyte data URI in the
                // user record. The avatar now goes through POST /profile/photo.
                'profile_photo_url' => $this->uploadAvatar($headers),
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

        // Real verification state for the portfolio tab. Null means the host
        // has never submitted documents — the page must say so instead of
        // asserting a fabricated "100% compliant".
        $verificationStatus = null;
        try {
            $vRes = $this->apiClient->get('/verification/owner', [], $headers);
            $verificationStatus = strtolower((string)(
                $vRes['verification']['status'] ?? ''
            )) ?: null;
        } catch (\Throwable $e) {
            $verificationStatus = null;
        }

        $stats = ['properties' => count($properties), 'rooms' => count($rooms)];
        $this->set(compact('userProfile', 'me', 'stats', 'verificationStatus'));
        return $this->render('/Pages/host-profile');
    }

    /**
     * Proxy upload endpoint for onboarding / host portal.
     * Uploads file to fastnet backend /upload with host authentication.
     */
    public function upload(): Response
    {
        $this->autoRender = false;
        $headers = $this->hostHeaders();
        $file = $this->getRequest()->getUploadedFile('file');
        // NOTE: CakePHP 5 UploadedFile (laminas-diactoros) has NO isValid()
        // method — getError() is the only validity check. Calling isValid()
        // fataled every valid upload (500 on /host/upload, Oct 2026).
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            $code = $file ? $file->getError() : UPLOAD_ERR_NO_FILE;
            $msg = match ($code) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Photo is too large (max 10 MB).',
                UPLOAD_ERR_PARTIAL => 'Upload was interrupted — please try again.',
                UPLOAD_ERR_NO_FILE => 'No photo selected.',
                default => 'No valid file uploaded.',
            };
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => $msg]));
        }
        // Match the wizard JS (10 MB) so Contabo php.ini slips give a clear message, not a silent fail.
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => 'Photo is too large (max 10 MB).']));
        }
        $mime = $file->getClientMediaType() ?: 'image/jpeg';
        if (!str_starts_with($mime, 'image/')) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => 'Only image files please.']));
        }
        $tmp = $file->getStream()->getMetadata('uri');
        if (!is_string($tmp) || !is_readable($tmp)) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => 'Could not read uploaded file.']));
        }
        $res = $this->apiClient->uploadFile(
            '/upload',
            'file',
            $tmp,
            $file->getClientFilename(),
            $mime,
            $headers,
            30
        );
        if ($res === null || (!empty($res['_status']) && (int)$res['_status'] >= 400)) {
            try {
                \Cake\Log\Log::error(sprintf(
                    '[Host upload] backend %s -> %s for %s (%s, %d bytes)',
                    $this->apiClient->getBaseUrl() . '/upload',
                    (string)($res['_status'] ?? 'no-response'),
                    $file->getClientFilename(),
                    $mime,
                    (int)$file->getSize()
                ));
            } catch (\Throwable $e) {
            }
            $status = (!empty($res['_status']) && (int)$res['_status'] >= 400) ? (int)$res['_status'] : 502;
            $msg = (string)($res['message'] ?? 'Upload service unavailable. Check BACKEND_API_URL and try again.');
            return $this->response
                ->withType('application/json')
                ->withStatus($status)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => $msg]));
        }
        return $this->response
            ->withType('application/json')
            ->withStatus(200)
            ->withStringBody((string)json_encode($res));
    }
}
