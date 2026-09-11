<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\StaysService;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;

/**
 * PagesController
 * Modular, clean controller for home, static presentation pages, and auth wrappers.
 */
class PagesController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected AuthService $authService;
    protected StaysService $staysService;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
        $this->staysService = new StaysService($this->apiClient);
    }

    public function beforeRender(\Cake\Event\EventInterface $event): void
    {
        parent::beforeRender($event);
        $session = $this->getRequest()->getSession();
        $isLoggedIn = $this->authService->isAuthenticated($session);
        $userProfile = null;
        if ($isLoggedIn) {
            $sessionUser = $session->read('User');
            $userProfile = !empty($sessionUser) ? $sessionUser : $this->authService->getPersonalDetails();
        }
        $this->set(compact('userProfile', 'isLoggedIn'));
    }

    /**
     * Displays a view
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }

    /**
     * Homepage - Google Hotels-style split-screen search + map
     * Reads search params from URL query string, fetches real properties, passes all to view.
     */
    public function index()
    {
        $input = $this->getRequest()->getQueryParams();
        $today = new \DateTimeImmutable('today');
        $searchErrors = [];

        // ── SPEC ALIASES: city ↔ destination, checkin ↔ checkIn, price_min ↔ min_price, etc.
        // Normalize incoming query to support both spec (?city=Arusha&checkin=…&price_min=…) and legacy (?destination=…&checkIn=…&min_price=…)
        $norm = function(string $spec, string $legacy) use ($input) {
            if (isset($input[$spec]) && $input[$spec] !== '') return $input[$spec];
            if (isset($input[$legacy]) && $input[$legacy] !== '') return $input[$legacy];
            return null;
        };
        $rawCity     = $norm('city', 'destination');
        // q fallback only if city not explicitly provided
        if ($rawCity === null && isset($input['q']) && trim((string)$input['q']) !== '') $rawCity = trim((string)$input['q']);
        $rawCheckIn  = $norm('checkin', 'checkIn');
        $rawCheckOut = $norm('checkout', 'checkOut');
        $rawMin      = $norm('price_min', 'min_price');
        $rawMax      = $norm('price_max', 'max_price');
        $rawPriceMin = $rawMin;
        $rawPriceMax = $rawMax;

        // Destination: spec graceful empty -> "All Tanzanian Destinations" (or geolocated city client-side)
        // Only default to Dar es Salaam on first load with no query at all; respect explicit empty string for "All"
        $destination = $rawCity !== null ? trim((string)$rawCity) : '';
        $isFirstLoad = empty($input);
        if ($destination === '' && $isFirstLoad) {
            $destination = 'Dar es Salaam';
        }
        if (mb_strlen($destination) > 120) {
            $destination = mb_substr($destination, 0, 120);
        }

        // Dates — validate Y-m-d, enforce 1-night min, disallow past
        $parseDate = function(?string $v): ?\DateTimeImmutable {
            if (!$v) return null;
            $d = \DateTimeImmutable::createFromFormat('Y-m-d', trim($v));
            return $d && $d->format('Y-m-d') === trim($v) ? $d : null;
        };
        $checkIn = $parseDate(is_string($rawCheckIn) ? $rawCheckIn : null);
        if (!$checkIn || $checkIn < $today) {
            if ($checkIn && $checkIn < $today) $searchErrors[] = 'Check-in was in the past — moved to ' . $today->modify('+7 days')->format('M j, Y') . '.';
            $checkIn = $today->modify('+7 days');
        }
        $checkOut = $parseDate(is_string($rawCheckOut) ? $rawCheckOut : null);
        if (!$checkOut || $checkOut <= $checkIn) {
            if ($rawCheckOut && $checkOut && $checkOut <= $checkIn) $searchErrors[] = 'Check-out must be after check-in — set to 1 night after check-in.';
            $checkOut = $checkIn->modify('+1 day');
        }

        // Guests — spec: adults 1-10, children 0-6, rooms 1-5
        $adults   = max(1, min(10, (int)($input['adults']   ?? 2)));
        $childrenRaw = (int)($input['children'] ?? 0);
        if ($childrenRaw > 6) { $searchErrors[] = 'Children capped at 6.'; $childrenRaw = 6; }
        $children = max(0, min(6, $childrenRaw));
        $roomsRaw = (int)($input['rooms']    ?? 1);
        if ($roomsRaw > 5) { $searchErrors[] = 'Rooms capped at 5.'; $roomsRaw = 5; }
        $rooms    = max(1, min(5, $roomsRaw));

        // Filters — dedupe, remove empty to keep URL clean
        $currentAmenities = [];
        if (!empty($input['amenities'])) {
            $currentAmenities = is_array($input['amenities'])
                ? $input['amenities']
                : explode(',', (string)$input['amenities']);
            $currentAmenities = array_values(array_unique(array_filter(array_map('trim', $currentAmenities))));
        }
        // property_type filter (spec: Hotel, Resort, Apartment, Safari Lodge, Villa)
        $propertyType = trim((string)($input['property_type'] ?? ''));
        if ($propertyType !== '' && !in_array($propertyType, ['Hotel','Resort','Apartment','Safari Lodge','Villa'], true)) {
            $propertyType = '';
        }
        $minPrice = $rawMin ?? '';
        $maxPrice = $rawMax ?? '';
        // validate price range
        if ($minPrice !== '' && $maxPrice !== '' && (float)$minPrice > (float)$maxPrice) {
            $searchErrors[] = 'Min price cannot exceed max price — swapped.';
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }
        $selectedRating = $input['rating']           ?? '';
        $freeCancel     = !empty($input['free_cancellation']);
        $sortBy         = $input['sort']             ?? 'recommended';
        // extended filters
        $paymentOpt     = trim((string)($input['payment'] ?? '')); // AzamPay / pay_at_property
        $mealsOpt       = trim((string)($input['meals'] ?? ''));
        $neighborhood   = trim((string)($input['neighborhood'] ?? ''));

        // Build normalized query params array (spec + legacy aliases)
        // When destination is empty, keep city='' to allow "All Tanzanian Destinations" state
        $displayCity = $destination !== '' ? $destination : '';
        $queryParams = [
            // canonical spec keys
            'city'             => $displayCity,
            'checkin'          => $checkIn->format('Y-m-d'),
            'checkout'         => $checkOut->format('Y-m-d'),
            'price_min'        => $minPrice,
            'price_max'        => $maxPrice,
            'property_type'    => $propertyType,
            'payment'          => $paymentOpt,
            'meals'            => $mealsOpt,
            'neighborhood'     => $neighborhood,
            // legacy aliases (templates still read these)
            'destination'      => $displayCity !== '' ? $displayCity : ($isFirstLoad ? 'Dar es Salaam' : ''),
            'checkIn'          => $checkIn->format('Y-m-d'),
            'checkOut'         => $checkOut->format('Y-m-d'),
            'min_price'        => $minPrice,
            'max_price'        => $maxPrice,
            'adults'           => $adults,
            'children'         => $children,
            'rooms'            => $rooms,
            'amenities'        => implode(',', $currentAmenities),
            'rating'           => $selectedRating,
            'free_cancellation'=> $freeCancel ? '1' : '',
            'sort'             => $sortBy,
            'lat'              => $input['lat'] ?? '',
            'lng'              => $input['lng'] ?? '',
        ];

        // Fetch real properties from API
        $apiPayload = [
            'q'       => $destination,
            'checkIn' => $queryParams['checkIn'],
            'checkOut'=> $queryParams['checkOut'],
            'adults'  => $adults,
            'children'=> $children,
            'rooms'   => $rooms,
        ];
        if (!empty($queryParams['lat']) && !empty($queryParams['lng'])) {
            $apiPayload['lat'] = $queryParams['lat'];
            $apiPayload['lng'] = $queryParams['lng'];
        }

        $properties = $this->staysService->searchProperties($apiPayload);

        // ── Real Mapbox token from backend (server-side, no CORS race) ──
        // Backend exposes GET /api/map-config → { mapbox_token: "pk.XXX" }
        // Falls back to env MAPBOX_TOKEN / Configure App.mapboxToken for prod
        // When no Mapbox token, fallback to free OSM style (demotiles) so map never shows "unavailable"
        $mapboxToken = null;
        $mapboxStyle = (string)Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
        $osmFallbackStyle = 'https://demotiles.maplibre.org/style.json';
        try {
            $cfg = $this->apiClient->get('/map-config');
            if (is_array($cfg)) {
                $candidate = $cfg['mapbox_token'] ?? $cfg['mapboxToken'] ?? $cfg['token'] ?? $cfg['access_token'] ?? null;
                if (!$candidate && isset($cfg['data']) && is_array($cfg['data'])) {
                    $candidate = $cfg['data']['mapbox_token'] ?? $cfg['data']['token'] ?? null;
                }
                if (is_string($candidate) && trim($candidate) !== '' && $candidate !== 'YOUR_MAPBOX_ACCESS_TOKEN' && $candidate !== 'pk.placeholder') {
                    $mapboxToken = trim($candidate);
                }
                if (!empty($cfg['mapbox_style']) && is_string($cfg['mapbox_style'])) {
                    $mapboxStyle = trim($cfg['mapbox_style']);
                } elseif (!empty($cfg['style']) && is_string($cfg['style'])) {
                    $mapboxStyle = trim($cfg['style']);
                }
            }
        } catch (\Throwable $e) {
            // silent — will use env fallback
        }
        if (!$mapboxToken) {
            $envToken = Configure::read('App.mapboxToken', env('MAPBOX_TOKEN', ''));
            if (is_string($envToken) && trim($envToken) !== '' && $envToken !== 'YOUR_MAPBOX_ACCESS_TOKEN' && $envToken !== 'pk.placeholder') {
                $mapboxToken = trim($envToken);
            }
        }
        // Final fallback: free OSM style guarantees map renders even when no Mapbox token configured
        if (!$mapboxToken) {
            $mapboxStyle = $osmFallbackStyle;
        }
        
        // MOCK DATA for development - Remove in production
        // Includes real lat/lng so Mapbox renders markers even when backend is offline
        if (empty($properties)) {
            $properties = [
                [
                    'id' => 1,
                    'name' => 'The Serena Hotel Dar es Salaam',
                    'city' => 'Dar es Salaam',
                    'latitude' => -6.7760, 'longitude' => 39.2828, 'lat' => -6.7760, 'lng' => 39.2828,
                    'image_url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=300&h=200&fit=crop',
                    'rating' => 4.7,
                    'review_count' => 285,
                    'price_per_night' => 85000,
                    'customer_price_per_night' => 85000,
                    'amenities' => ['WiFi', 'Pool', 'Fitness Center', 'Restaurant', 'Breakfast'],
                    'description' => 'Luxury 5-star hotel in the heart of Dar es Salaam with oceanfront views'
                ],
                [
                    'id' => 2,
                    'name' => 'Hyatt Regency Dar es Salaam',
                    'city' => 'Dar es Salaam',
                    'latitude' => -6.8010, 'longitude' => 39.2833, 'lat' => -6.8010, 'lng' => 39.2833,
                    'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=300&h=200&fit=crop',
                    'rating' => 4.5,
                    'review_count' => 156,
                    'price_per_night' => 72000,
                    'customer_price_per_night' => 72000,
                    'amenities' => ['WiFi', 'Pool', 'Gym', 'Bar', 'Business Center'],
                    'description' => '4-star hotel with modern amenities and excellent service'
                ],
                [
                    'id' => 3,
                    'name' => 'Dar Boutique Hotel',
                    'city' => 'Dar es Salaam',
                    'latitude' => -6.7690, 'longitude' => 39.2500, 'lat' => -6.7690, 'lng' => 39.2500,
                    'image_url' => 'https://images.unsplash.com/photo-1570129477492-45a003537e1f?w=300&h=200&fit=crop',
                    'rating' => 4.3,
                    'review_count' => 98,
                    'price_per_night' => 45000,
                    'customer_price_per_night' => 45000,
                    'amenities' => ['WiFi', 'Breakfast', 'Air Conditioning', 'Restaurant'],
                    'description' => 'Charming boutique hotel in Stone Town with personalized service'
                ],
                [
                    'id' => 4,
                    'name' => 'Addax Hotel Dar es Salaam',
                    'city' => 'Dar es Salaam',
                    'latitude' => -6.7924, 'longitude' => 39.2083, 'lat' => -6.7924, 'lng' => 39.2083,
                    'image_url' => 'https://images.unsplash.com/photo-1564078516801-18a1ab35eca3?w=300&h=200&fit=crop',
                    'rating' => 4.4,
                    'review_count' => 203,
                    'price_per_night' => 55000,
                    'customer_price_per_night' => 55000,
                    'amenities' => ['WiFi', 'Pool', 'Air Conditioning', 'Parking'],
                    'description' => 'Mid-range hotel with great value for money and friendly staff'
                ],
                [
                    'id' => 5,
                    'name' => 'Oceanview Hotel & Resort',
                    'city' => 'Zanzibar',
                    'latitude' => -6.1659, 'longitude' => 39.2026, 'lat' => -6.1659, 'lng' => 39.2026,
                    'image_url' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=300&h=200&fit=crop',
                    'rating' => 4.6,
                    'review_count' => 412,
                    'price_per_night' => 95000,
                    'customer_price_per_night' => 95000,
                    'amenities' => ['WiFi', 'Pool', 'Beach Access', 'Spa', 'Restaurant', 'Fitness'],
                    'description' => 'Premier resort with private beach, spa, and world-class dining'
                ],
                [
                    'id' => 6,
                    'name' => 'Safari Palace Hotel',
                    'city' => 'Arusha',
                    'latitude' => -3.3869, 'longitude' => 36.6820, 'lat' => -3.3869, 'lng' => 36.6820,
                    'image_url' => 'https://images.unsplash.com/photo-1559599810-46d1c52494ee?w=300&h=200&fit=crop',
                    'rating' => 4.2,
                    'review_count' => 167,
                    'price_per_night' => 38000,
                    'customer_price_per_night' => 38000,
                    'amenities' => ['WiFi', 'Air Conditioning', 'Restaurant', 'Breakfast'],
                    'description' => 'Comfortable budget-friendly hotel perfect for travelers'
                ]
            ];
        }

        // Client-side amenity filter
        if (!empty($currentAmenities)) {
            $properties = array_values(array_filter($properties, function ($prop) use ($currentAmenities) {
                $rawAm = $prop['amenities'] ?? [];
                if (is_string($rawAm)) {
                    $decoded = json_decode($rawAm, true);
                    $propAms = is_array($decoded) ? $decoded : explode(',', $rawAm);
                } else {
                    $propAms = is_array($rawAm) ? $rawAm : [];
                }
                $propAmText = strtolower(implode(' ', $propAms) . ' ' . ($prop['description'] ?? ''));
                foreach ($currentAmenities as $req) {
                    if (!empty($req) && !str_contains($propAmText, strtolower($req))) {
                        return false;
                    }
                }
                return true;
            }));
        }
        if ($minPrice !== '') {
            $properties = array_values(array_filter($properties, fn($p) => ((float)($p['price_per_night'] ?? ($p['price'] ?? 0))) >= (float)$minPrice));
        }
        if ($maxPrice !== '') {
            $properties = array_values(array_filter($properties, fn($p) => ((float)($p['price_per_night'] ?? ($p['price'] ?? 0))) <= (float)$maxPrice));
        }
        if ($selectedRating !== '') {
            $properties = array_values(array_filter($properties, fn($p) => ((float)($p['reviews_avg_rating'] ?? ($p['rating'] ?? 8.5))) >= (float)$selectedRating));
        }
        if ($freeCancel) {
            $properties = array_values(array_filter($properties, fn($p) => !empty($p['free_cancellation']) || (!empty($p['cancellation_policy']) && stripos((string)$p['cancellation_policy'], 'free') !== false)));
        }
        // Property type filter (spec: Hotel, Resort, Apartment, Safari Lodge, Villa)
        if ($propertyType !== '') {
            $properties = array_values(array_filter($properties, function($p) use ($propertyType){
                $pt = strtolower((string)($p['property_type'] ?? ($p['type'] ?? '')));
                $needle = strtolower($propertyType);
                // allow partial match: "Safari Lodge" should match description/type
                $hay = strtolower(($p['property_type'] ?? '') . ' ' . ($p['type'] ?? '') . ' ' . ($p['description'] ?? '') . ' ' . ($p['name'] ?? ''));
                return str_contains($hay, $needle) || $pt === $needle;
            }));
        }
        // Extended filters (meals/payment/neighborhood) — soft filter if mock data lacks fields
        if ($paymentOpt !== '' && $paymentOpt === 'pay_at_property') {
            // if property explicitly marks pay_at_property, filter; otherwise keep all (mock data neutral)
            $hasAny = count(array_filter($properties, fn($p)=>!empty($p['pay_at_property'])))>0;
            if ($hasAny) $properties = array_values(array_filter($properties, fn($p)=>!empty($p['pay_at_property'])));
        }

        $totalCount = count($properties);

        // JSON hydration for FastNetState AJAX — ?format=json
        if (($this->getRequest()->getQuery('format') ?? '') === 'json') {
            $view = $this->createView($this->viewBuilder()->getClassName());
            $view->set(['properties' => $properties, 'queryParams' => $queryParams, 'totalCount' => $totalCount, 'destination' => $destination]);
            $html = $view->element('Home/gh-hotel-cards', ['properties' => $properties, 'queryParams' => $queryParams, 'totalCount' => $totalCount, 'destination' => $destination]);
            $markers = [];
            foreach ($properties as $p) {
                $lat = (float)($p['latitude'] ?? ($p['lat'] ?? 0));
                $lng = (float)($p['longitude'] ?? ($p['lng'] ?? 0));
                if ($lat == 0 && $lng == 0) continue;
                $price = (int)($p['customer_price_per_night'] ?? ($p['price_per_night'] ?? ($p['price'] ?? 0)));
                $markers[] = ['id' => (int)($p['id'] ?? 0), 'lat' => $lat, 'lng' => $lng, 'label' => 'TSH ' . number_format($price), 'title' => $p['name'] ?? ''];
            }
            $payload = ['html' => $html, 'markers' => $markers, 'totalCount' => $totalCount, 'queryParams' => $queryParams, 'mapboxToken' => $mapboxToken, 'mapboxStyle' => $mapboxStyle];
            return $this->response->withType('application/json')->withStringBody((string)json_encode($payload));
        }

        $this->set(compact(
            'properties', 'queryParams', 'totalCount', 'searchErrors',
            'destination', 'currentAmenities', 'minPrice', 'maxPrice',
            'selectedRating', 'freeCancel', 'sortBy', 'mapboxToken', 'mapboxStyle'
        ));
        return $this->render('/Pages/index');
    }

    // ── Stays Forwarders (Backward Compatibility) ──────────────────────────
    public function hotelList01()
    {
        return $this->redirect('/?' . http_build_query($this->getRequest()->getQueryParams()));
    }

    public function hotelDetail($id = null)
    {
        return $this->redirect(['controller' => 'Stays', 'action' => 'detail', $id, '?' => $this->getRequest()->getQueryParams()]);
    }

    public function destination01()
    {
        return $this->redirect(['controller' => 'Stays', 'action' => 'destination01', '?' => $this->getRequest()->getQueryParams()]);
    }

    // ── Host Onboarding ───────────────────────────────────────────────────
    public function joinUs() { return $this->render('/Pages/join-us'); }
    public function addListing() { return $this->render('/Pages/add-listing'); }
    public function addListingStep02() { return $this->render('/Pages/add-listing-step-02'); }
    public function addListingStep03() { return $this->render('/Pages/add-listing-step-03'); }

    // ── Bookings Forwarders (Backward Compatibility) ───────────────────────
    public function bookingPage()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingPage', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function bookingpage02()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingpage02', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function bookingpage03()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingpage03', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function bookingpageSuccess()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingpageSuccess', '?' => $this->getRequest()->getQueryParams()]);
    }

    // ── Account Forwarders (Backward Compatibility) ────────────────────────
    public function menu() { return $this->redirect(['controller' => 'Account', 'action' => 'menu']); }
    public function myProfile() { return $this->redirect(['controller' => 'Account', 'action' => 'myProfile']); }
    public function accountSecurity() { return $this->redirect(['controller' => 'Account', 'action' => 'accountSecurity']); }
    public function myBooking() { return $this->redirect(['controller' => 'Account', 'action' => 'myBooking']); }
    public function paymentDetail() { return $this->redirect(['controller' => 'Account', 'action' => 'paymentDetail']); }
    public function myWishlists() { return $this->redirect(['controller' => 'Account', 'action' => 'myWishlists']); }
    public function recentlyViewed() { return $this->redirect(['controller' => 'Account', 'action' => 'recentlyViewed']); }
    public function searchPreferences() { return $this->redirect(['controller' => 'Account', 'action' => 'searchPreferences']); }
    public function notifications() { return $this->redirect(['controller' => 'Account', 'action' => 'notifications']); }
    public function languageAndCurrency() { return $this->redirect(['controller' => 'Account', 'action' => 'languageAndCurrency']); }
    public function settings() { return $this->redirect(['controller' => 'Account', 'action' => 'settings']); }
    public function deleteAccount() { return $this->redirect(['controller' => 'Account', 'action' => 'deleteAccount']); }

    // ── Authentication Flow ───────────────────────────────────────────────
    public function login()
    {
        $session = $this->getRequest()->getSession();

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();

            // 1. Handle AJAX Session Synchronization (from login.php fetch)
            if (!empty($data['action']) && $data['action'] === 'login_sync' && !empty($data['user'])) {
                $user = (array)$data['user'];
                $token = (string)($data['token'] ?? ($data['access_token'] ?? ''));
                if (!empty($token)) {
                    $user['token'] = $token;
                }
                if (empty($user['first_name']) && !empty($user['name'])) {
                    $user['first_name'] = explode(' ', trim($user['name']))[0];
                }
                $this->authService->syncSession($session, $user);

                return $this->response->withType('application/json')->withStringBody((string)json_encode([
                    'success' => true,
                    'user' => $user
                ]));
            }

            // 2. Handle Traditional Form Login
            $email = (string)($data['email'] ?? '');
            $password = (string)($data['password'] ?? '');

            if (!empty($email) && !empty($password)) {
                $res = $this->authService->login($email, $password);
                $authToken = $res['access_token'] ?? ($res['token'] ?? null);
                $userData = $res['user'] ?? null;

                if (!empty($userData) || !empty($authToken)) {
                    $user = is_array($userData) ? $userData : [];
                    if ($authToken) {
                        $user['token'] = $authToken;
                    }
                    if (empty($user['first_name']) && !empty($user['name'])) {
                        $user['first_name'] = explode(' ', trim($user['name']))[0];
                    }
                    $this->authService->syncSession($session, $user);
                    $this->Flash->success(__('Login successful. Welcome back!'));
                    return $this->redirect('/');
                }
                $this->Flash->error(__('Invalid email or password.'));
            }
        }

        return $this->render('/Pages/login');
    }

    public function signup()
    {
        if ($this->getRequest()->is('post')) {
            $data = $this->getRequest()->getData();
            $this->Flash->success(__('Account created successfully! Welcome to fastnetstays.com.'));
            return $this->redirect('/login');
        }
        return $this->render('/Pages/signup');
    }

    public function forgotPassword() { return $this->render('/Pages/forgot-password'); }
    public function twoFactorAuth() { return $this->render('/Pages/two-factor-auth'); }
    public function resetPassword() { return $this->render('/Pages/reset-password'); }

    // ── Static & Support Pages ────────────────────────────────────────────
    public function aboutUs() { return $this->render('/Pages/about-us'); }
    public function howWeWork() { return $this->render('/Pages/how-we-work'); }
    public function helpCenter() { return $this->render('/Pages/help-center'); }
    public function faq() { return $this->render('/Pages/faq'); }
    public function notFound() { return $this->render('/Pages/404'); }
    public function privacyPolicy() { return $this->render('/Pages/privacy-policy'); }
    public function termsOfService() { return $this->render('/Pages/terms-of-service'); }
    public function contactV1() { return $this->render('/Pages/contact-v1'); }

    // ── Universal API Proxy (localhost & production) ─────────────────────
    public function apiProxy(string ...$path): Response
    {
        $apiPath = '/' . implode('/', $path);
        $method = strtolower($this->getRequest()->getMethod());
        $queryParams = $this->getRequest()->getQueryParams();
        $body = $this->getRequest()->getData();

        if ($method === 'post') {
            $data = $this->apiClient->post($apiPath, (array)$body);
        } else {
            $data = $this->apiClient->get($apiPath, $queryParams);
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode($data ?? []));
    }
}
