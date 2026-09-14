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
}
