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
    }

    private function hostHeaders(): array
    {
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        return $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Require login for all host actions — portal page-login.php:18 POST /api/login → Bearer
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        $user = $this->getRequest()->getSession()->read('User');
        if ($token === '' || empty($user)) {
            $this->Flash->error(__('Please sign in to access Host Dashboard.'));
            return $this->redirect('/login');
        }
    }

    public function dashboard()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        // Mirror admin_owner_portal/index.php:12 — admin sees all, owner filtered by host_id if backend respects token
        $propRes = $this->apiClient->get('/admin/properties', [], $headers);
        if (empty($propRes) || !empty($propRes['_status'])) {
            $propRes = $this->apiClient->get('/properties', [], $headers);
        }
        $properties = $propRes['data'] ?? (isset($propRes[0]) ? $propRes : []);
        if (!is_array($properties)) $properties = [];

        $bookRes = $this->apiClient->get('/admin/bookings', [], $headers);
        $bookings = $bookRes['data'] ?? (isset($bookRes[0]) ? $bookRes : []);
        if (!is_array($bookings)) $bookings = [];

        $stats = [
            'properties' => count($properties),
            'bookings' => count($bookings),
            'revenue' => array_sum(array_map(fn($b) => (float)($b['total_price'] ?? 0), $bookings)),
        ];
        $this->set(compact('userProfile', 'properties', 'bookings', 'stats'));
        return $this->render('/Pages/host-dashboard');
    }

    public function listings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $res = $this->apiClient->get('/properties', [], $headers);
        $properties = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($properties)) $properties = [];
        $this->set(compact('userProfile', 'properties'));
        return $this->render('/Pages/host-listings');
    }

    public function create()
    {
        $headers = $this->hostHeaders();
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $payload = [
                'name' => trim((string)($data['name'] ?? '')),
                'description' => trim((string)($data['description'] ?? '')),
                'address' => trim((string)($data['address'] ?? '')),
                'city' => trim((string)($data['city'] ?? 'Dar es Salaam')),
                'area' => trim((string)($data['area'] ?? '')),
                'price_per_night' => (float)($data['price_per_night'] ?? 0),
                'latitude' => (float)($data['latitude'] ?? -6.7924),
                'longitude' => (float)($data['longitude'] ?? 39.2083),
                'image_url' => trim((string)($data['image_url'] ?? '')),
            ];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Name and price are required.'));
            } else {
                $res = $this->apiClient->post('/properties', $payload, $headers);
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not create listing.'));
                } else {
                    $this->Flash->success(__('Property created.'));
                    return $this->redirect(['action' => 'listings']);
                }
            }
        }
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $this->set(compact('userProfile'));
        return $this->render('/Pages/host-listing-form');
    }

    public function bookings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $res = $this->apiClient->get('/admin/bookings', [], $headers);
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->apiClient->get('/bookings', [], $headers);
        }
        $bookings = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($bookings)) $bookings = [];
        $this->set(compact('userProfile', 'bookings'));
        return $this->render('/Pages/host-bookings');
    }

    public function calendar(?int $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $property = null;
        $rooms = [];
        if ($id) {
            $res = $this->apiClient->get('/properties/' . $id, [], $headers);
            $property = $res['data'] ?? $res;
            if (empty($property) || !empty($property['_status'])) {
                $this->Flash->error(__('Property not found.'));
                return $this->redirect(['action' => 'listings']);
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
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
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
        $this->set(compact('userProfile', 'finance', 'payouts'));
        return $this->render('/Pages/host-earnings');
    }

    // ---- Working-only additions mirroring admin_owner_portal ----

    public function rooms()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $search = trim((string)$this->getRequest()->getQuery('search', ''));
        $status = trim((string)$this->getRequest()->getQuery('status', ''));

        // Fetch properties for owner filter + dropdown
        $pRes = $this->apiClient->get('/properties', [], $headers);
        $properties = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
        if (!is_array($properties)) $properties = [];

        // Fetch all rooms
        $rRes = $this->apiClient->get('/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];
        // Also try per-property fallback if /rooms empty
        if (empty($rooms) && !empty($properties)) {
            $agg = [];
            foreach (array_slice($properties, 0, 8) as $p) {
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

    public function addRoom()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        $pRes = $this->apiClient->get('/properties', [], $headers);
        $properties = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
        if (!is_array($properties)) $properties = [];

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $propertyId = (int)($data['property_id'] ?? 0);
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
                    'amenities' => array_values(array_filter(array_map('trim', (array)($data['amenities'] ?? [])))),
                    'photos' => array_values(array_filter(array_map('trim', (array)($data['photos'] ?? [])))),
                ];
                if ($payload['room_number'] === '' || $payload['price'] <= 0) {
                    $this->Flash->error(__('Room number and price are required.'));
                } else {
                    $res = $this->apiClient->post('/properties/' . $propertyId . '/rooms', $payload, $headers);
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
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $roomId = (int)$id;
        if ($roomId <= 0) return $this->redirect(['action' => 'rooms']);

        // Fetch room
        $rRes = $this->apiClient->get('/rooms/' . $roomId, [], $headers);
        $room = $rRes['data'] ?? $rRes;
        if (empty($room) || !empty($room['_status']) || empty($room['id'])) {
            $this->Flash->error(__('Room not found.'));
            return $this->redirect(['action' => 'rooms']);
        }

        $pRes = $this->apiClient->get('/properties', [], $headers);
        $properties = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
        if (!is_array($properties)) $properties = [];

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
            if (isset($data['amenities'])) $payload['amenities'] = array_values(array_filter(array_map('trim', (array)$data['amenities'])));
            if (isset($data['photos'])) $payload['photos'] = array_values(array_filter(array_map('trim', (array)$data['photos'])));

            $res = $this->apiClient->put('/rooms/' . $roomId, $payload, $headers);
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
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
        $propId = $id !== null ? (int)$id : null;

        $pRes = $this->apiClient->get('/properties', [], $headers);
        $properties = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
        if (!is_array($properties)) $properties = [];

        $property = null;
        if ($propId) {
            $r = $this->apiClient->get('/properties/' . $propId, [], $headers);
            $property = $r['data'] ?? $r;
            if (empty($property) || !empty($property['_status'])) $property = null;
        }
        if (!$property && !empty($properties)) $property = $properties[0];
        if (!$property) {
            $this->Flash->error(__('No property found. Create one first.'));
            return $this->redirect(['action' => 'listings']);
        }
        $propId = (int)($property['id'] ?? $propId);

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
                'amenities' => array_values(array_filter(array_map('trim', (array)($data['amenities'] ?? $property['amenities'] ?? [])))),
            ];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Name and price are required.'));
            } else {
                $res = $this->apiClient->put('/properties/' . $propId, $payload, $headers);
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update lodge.'));
                } else {
                    $this->Flash->success(__('Lodge updated.'));
                    return $this->redirect(['action' => 'listings']);
                }
            }
        }

        $this->set(compact('userProfile', 'property', 'rooms'));
        return $this->render('/Pages/host-lodge-form');
    }

    public function onboarding()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $payload = [
                'name' => trim((string)($data['name'] ?? '')),
                'description' => trim((string)($data['description'] ?? '')),
                'address' => trim((string)($data['address'] ?? '')),
                'city' => trim((string)($data['city'] ?? 'Dar es Salaam')),
                'area' => trim((string)($data['area'] ?? '')),
                'price_per_night' => (float)($data['price_per_night'] ?? 0),
                'latitude' => (float)($data['latitude'] ?? -6.7924),
                'longitude' => (float)($data['longitude'] ?? 39.2083),
                'image_url' => trim((string)($data['image_url'] ?? '')),
                'amenities' => array_values(array_filter(array_map('trim', (array)($data['amenities'] ?? [])))),
            ];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Name and price are required.'));
            } else {
                $res = $this->apiClient->post('/properties', $payload, $headers);
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not create property.'));
                } else {
                    $this->Flash->success(__('Property onboarded. Add rooms next.'));
                    return $this->redirect(['action' => 'rooms']);
                }
            }
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/host-onboarding');
    }

    public function profile()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
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
                $res = $this->apiClient->put('/user/profile', $payload, $headers);
                // Fallback /profile
                if (!empty($res['_status']) && (int)$res['_status'] === 404) {
                    $res = $this->apiClient->patch('/profile', $payload, $headers);
                }
                if ($res === null) {
                    $this->Flash->error(__('Could not update profile — service unavailable.'));
                } elseif (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update profile.'));
                } else {
                    $this->Flash->success(__('Profile updated.'));
                    // Refresh
                    $userProfile = $this->authService->getPersonalDetails($headers['Authorization'] ?? '');
                    $me = $userProfile;
                }
            }
        }

        // Stats for header (properties/rooms)
        $pRes = $this->apiClient->get('/properties', [], $headers);
        $properties = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
        if (!is_array($properties)) $properties = [];
        $rRes = $this->apiClient->get('/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];

        $stats = ['properties' => count($properties), 'rooms' => count($rooms)];
        $this->set(compact('userProfile', 'me', 'stats'));
        return $this->render('/Pages/host-profile');
    }
}
