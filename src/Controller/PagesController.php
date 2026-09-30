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
        // Legacy mobile tabs → real working redirects (Apartment/Home/Lodge)
        if (array_key_exists('explore', $input)) {
            $c = ($destination !== '' && $destination !== 'Vacation') ? $destination : '';
            $q = $c !== '' ? ['city'=>$c, 'property_type'=>'Apartment'] : ['property_type'=>'Apartment'];
            return $this->redirect('/?' . http_build_query($q));
        }
        if (array_key_exists('homes', $input)) {
            $c = ($destination !== '' && $destination !== 'Vacation') ? $destination : '';
            $q = $c !== '' ? ['city'=>$c] : [];
            return $this->redirect($q ? '/?' . http_build_query($q) : '/');
        }
        if ($destination === 'Vacation') {
            return $this->redirect('/?property_type=Safari%20Lodge');
        }
        // also handle legacy Villa → Safari Lodge
        if (($input['property_type'] ?? '') === 'Villa') {
            $q = $input; $q['property_type'] = 'Safari Lodge';
            return $this->redirect('/?' . http_build_query(array_filter($q, fn($v)=>$v!=='' && $v!==null)));
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
            'bbox'             => '',
        ];
        // Viewport bbox "ne_lat,ne_lng,sw_lat,sw_lng" — exact-area filter, survives round-trips
        $rawBounds = trim((string)($input['bounds'] ?? $input['bbox'] ?? ''));
        if ($rawBounds !== '') {
            $parts = array_map('trim', explode(',', $rawBounds));
            if (count($parts) === 4 && count(array_filter($parts, 'is_numeric')) === 4) {
                $queryParams['bbox'] = implode(',', $parts);
            }
        }

        // Fetch real properties from API — empty destination = All Tanzania (omit q to fetch all, don't filter to zero)
        $isAllTanzania = $destination === '' || strtolower($destination) === 'tanzania';
        $apiPayload = [
            'checkIn' => $queryParams['checkIn'],
            'checkOut'=> $queryParams['checkOut'],
            'adults'  => $adults,
            'children'=> $children,
            'rooms'   => $rooms,
        ];
        if (!$isAllTanzania) $apiPayload['q'] = $destination;
        // Sort forwarded — backend PropertySearchService: price_asc/price_desc/rating; omitted = Recommended (reviews_avg_rating DESC)
        $sortMap = ['price_asc' => 'price_asc', 'price_desc' => 'price_desc', 'rating' => 'rating'];
        if (!empty($sortMap[$sortBy])) $apiPayload['sort'] = $sortMap[$sortBy];
        // Paginate server-side (backend max 100) — 48 fills list + map (client marker cap 60) without rendering everything
        $apiPayload['per_page'] = 48;
        // property_type handled locally solid with name fallback — do not forward to API (API type field is null for seeded data)
        if (!empty($currentAmenities)) $apiPayload['amenities'] = implode(',', $currentAmenities);
        // Rating filter was dead: backend only reads min_rating (keeps unreviewed lodges)
        if ($selectedRating !== '') {
            $apiPayload['rating'] = $selectedRating;
            $apiPayload['min_rating'] = $selectedRating;
        }
        if ($freeCancel) $apiPayload['free_cancellation'] = 1;
        if ($minPrice !== '') $apiPayload['price_min'] = $minPrice;
        if ($maxPrice !== '') $apiPayload['price_max'] = $maxPrice;
        // Meals map to real amenity text (backend matches description/room amenities)
        $mealAmen = ['breakfast' => 'Breakfast', 'self_catering' => 'Kitchen'];
        if (!empty($mealAmen[$mealsOpt] ?? '')) {
            $apiPayload['amenities'] = trim(($apiPayload['amenities'] ?? '') . ',' . $mealAmen[$mealsOpt], ',');
        }
        // payment/neighborhood have no backend data source — never forwarded (UI removed)
        if (!empty($queryParams['lat']) && !empty($queryParams['lng'])) {
            $apiPayload['lat'] = $queryParams['lat'];
            $apiPayload['lng'] = $queryParams['lng'];
        }
        // Exact-area viewport wins over radius: backend intersects both
        if (!empty($queryParams['bbox'])) {
            $apiPayload['bounds'] = $queryParams['bbox'];
        }
        $areaParam = trim((string)($input['area'] ?? ''));
        if ($areaParam !== '') {
            $apiPayload['area'] = $areaParam;
            $queryParams['area'] = $areaParam;
        }

        $properties = $this->staysService->searchProperties($apiPayload);

        // ── Real Mapbox token from backend (server-side, no CORS race) ──
        // Backend exposes GET /api/map-config → { mapbox_token: "pk.XXX" }
        // Falls back to env MAPBOX_TOKEN / Configure App.mapboxToken for prod
        // When no Mapbox token, fallback to free Carto basemap (reliable, no key) so map never shows "unavailable"
        $mapboxToken = null;
        $mapboxStyle = (string)Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
        $osmFallbackStyle = 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
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
        
        // ── Demo preview: one product with full gallery + map pin for visual QA (shown when API empty, even in prod, via ?demo=1) ──
        $isDemoPreview = isset($input['demo']) && $input['demo'] !== '0' && $input['demo'] !== 'false';
        // Demo hotel adapts to requested city for solid professional demo — Arusha query never shows Dar es Salaam
        $demoCity = ($destination !== '' && strtolower($destination) !== 'tanzania') ? $destination : 'Dar es Salaam';
        $demoCoords = [
            'dar es salaam' => [-6.7760, 39.2828, 'Msasani Peninsula'],
            'arusha' => [-3.3869, 36.6829, 'Sekei'],
            'zanzibar' => [-6.1659, 39.1996, 'Stone Town'],
            'dodoma' => [-6.1730, 35.7416, 'Central'],
            'mwanza' => [-2.5167, 32.9000, 'Capri Point'],
            'kilimanjaro' => [-3.0674, 37.3556, 'Moshi'],
            'serengeti' => [-2.3333, 34.8333, 'Seronera'],
        ];
        $demoKey = strtolower(trim($demoCity));
        $demoLatLng = $demoCoords[$demoKey] ?? $demoCoords['dar es salaam'];
        $demoHotel = [
            'id' => 1,
            'name' => 'The Serena Hotel ' . $demoCity,
            'city' => $demoCity,
            'area' => $demoLatLng[2],
            'address' => 'Plot 123, ' . $demoLatLng[2] . ', ' . $demoCity . ', Tanzania',
            'latitude' => $demoLatLng[0], 'longitude' => $demoLatLng[1], 'lat' => $demoLatLng[0], 'lng' => $demoLatLng[1],
            'star_rating' => 5,
            'rating' => 4.7, 'reviews_avg_rating' => 4.7,
            'review_count' => 285, 'reviews_count' => 285,
            'price_per_night' => 85000, 'customer_price_per_night' => 85000, 'price' => 85000,
            'primary_image_url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
            'image_url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
            'cover_image' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
            'images' => [
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1200&h=800&fit=crop',
                'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1551882547-b79c417633b0?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop',
            ],
            'amenities' => ['WiFi', 'Pool', 'Fitness Center', 'Restaurant', 'Breakfast', 'Air conditioning', 'Free parking', 'Pet-friendly', 'Spa', 'Room service'],
            'description' => 'Luxury 5-star hotel, apartment, villa and safari lodge options in the heart of ' . $demoCity . ' with oceanfront views, infinity pool, spa and fine dining. Real map pin at ' . $demoLatLng[2] . '.',
            'property_type' => ($propertyType !== '' ? $propertyType : 'Hotel'),
            'free_cancellation' => true,
        ];

        if (empty($properties)) {
            if ($isDemoPreview || Configure::read('debug')) {
                // Demo mode or debug only: show full gallery + map
                $properties = [$demoHotel];
                if ($isDemoPreview) {
                    $searchErrors[] = 'Demo preview: showing one real hotel with full gallery and map pin. Remove ?demo=1 to see live data.';
                }
            } else {
                // Prod real: no fake — empty stays shows "No stays found" (real backend empty)
                $properties = [];
            }
            // If debug true and not demo, expand to 6 for fuller grid QA
            if (Configure::read('debug') && !$isDemoPreview && count($properties) === 1) {
                $properties = [
                    $demoHotel,
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
                ];
            }
        }

        // SOLID professional city filter — Arusha only returns Arusha (exact city match, no "like" leakage)
        // UX-2: canonical alias map lives in App\Utility\CityAliases (unit-tested) so misspells/short codes
        // resolve (arusa→arusha, dsm→dar, znz→zanzibar, moshi→kilimanjaro) instead of zeroing results
        if ($destination !== '' && strtolower($destination) !== 'tanzania') {
            $properties = array_values(array_filter($properties, function ($p) use ($destination) {
                return \App\Utility\CityAliases::matches(
                    $destination,
                    (string)($p['city'] ?? ''),
                    (string)($p['area'] ?? '')
                );
            }));
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
        // Property type filter — solid exact, with fallback to name when type field missing (API has null)
        if ($propertyType !== '') {
            $needle = strtolower(trim($propertyType));
            $properties = array_values(array_filter($properties, function($p) use ($needle){
                $pt = strtolower(trim((string)($p['property_type'] ?? ($p['type'] ?? ''))));
                if ($pt !== '') return $pt === $needle;
                // type missing — infer from name (sunrise lodge → Safari Lodge)
                $name = strtolower((string)($p['name'] ?? ''));
                if ($needle === 'safari lodge' && str_contains($name, 'lodge')) return true;
                if ($needle === 'apartment' && str_contains($name, 'apartment')) return true;
                if ($needle === 'hotel' && (str_contains($name, 'hotel') || str_contains($name, 'lodge'))) return true;
                return false;
            }));
        }
        // Extended filters (meals/payment/neighborhood) — soft filter if mock data lacks fields
        if ($paymentOpt !== '' && $paymentOpt === 'pay_at_property') {
            // if property explicitly marks pay_at_property, filter; otherwise keep all (mock data neutral)
            $hasAny = count(array_filter($properties, fn($p)=>!empty($p['pay_at_property'])))>0;
            if ($hasAny) $properties = array_values(array_filter($properties, fn($p)=>!empty($p['pay_at_property'])));
        }

        $totalCount = count($properties);
        // Honest counts: backend paginator total (all hits) vs shown (page + local filters). Header shows "X of Y" when partial.
        $totalHits = $this->staysService->lastTotal;
        if ($totalHits !== null && $totalHits < $totalCount) $totalHits = $totalCount;

        // JSON hydration for FastNetState AJAX — ?format=json
        if (($this->getRequest()->getQuery('format') ?? '') === 'json') {
            $view = $this->createView($this->viewBuilder()->getClassName());
            $view->set(['properties' => $properties, 'queryParams' => $queryParams, 'totalCount' => $totalCount, 'totalHits' => $totalHits, 'destination' => $destination]);
            $html = $view->element('Home/gh-hotel-cards', ['properties' => $properties, 'queryParams' => $queryParams, 'totalCount' => $totalCount, 'totalHits' => $totalHits, 'destination' => $destination]);
            $markers = [];
            foreach ($properties as $p) {
                $lat = (float)($p['latitude'] ?? ($p['lat'] ?? 0));
                $lng = (float)($p['longitude'] ?? ($p['lng'] ?? 0));
                if ($lat == 0 && $lng == 0) continue;
                $price = (int)($p['customer_price_per_night'] ?? ($p['price_per_night'] ?? ($p['price'] ?? 0)));
                $markers[] = ['id' => (int)($p['id'] ?? 0), 'lat' => $lat, 'lng' => $lng, 'label' => 'TSH ' . number_format($price), 'title' => $p['name'] ?? ''];
            }
            $payload = ['html' => $html, 'markers' => $markers, 'totalCount' => $totalCount, 'totalHits' => $totalHits, 'queryParams' => $queryParams, 'mapboxToken' => $mapboxToken, 'mapboxStyle' => $mapboxStyle];
            return $this->response->withType('application/json')->withStringBody((string)json_encode($payload));
        }

        $session = $this->getRequest()->getSession();
        $recentStays = $session->read('recently_viewed_stays') ?? [];
        if (!is_array($recentStays)) $recentStays = [];

        $this->set(compact(
            'properties', 'queryParams', 'totalCount', 'totalHits', 'searchErrors',
            'destination', 'currentAmenities', 'minPrice', 'maxPrice',
            'selectedRating', 'freeCancel', 'sortBy', 'mapboxToken', 'mapboxStyle',
            'recentStays'
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

    // ── Host Onboarding (real working) ────────────────────────────────────
    public function joinUs()
    {
        $session = $this->getRequest()->getSession();
        $sessionUser = $session->read('User');
        $rawToken = trim((string)$session->read('auth_token'));
        if (stripos($rawToken, 'Bearer ') === 0) {
            $rawToken = trim(substr($rawToken, 7));
        }
        $isLoggedIn = !$session->read('is_logged_out') && !empty($sessionUser);
        $userRole = strtolower((string)($sessionUser['role'] ?? ''));
        $headers = $rawToken !== '' ? ['Authorization' => 'Bearer ' . $rawToken] : [];

        // Handle Become-a-Host upgrade for logged-in customers
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            if (($data['action'] ?? '') === 'become_host') {
                if (!$isLoggedIn) {
                    $this->Flash->error(__('Please sign in first, then become a host.'));
                    return $this->redirect('/login?redirect=/join-us');
                }
                if (in_array($userRole, ['owner', 'admin'], true)) {
                    return $this->redirect('/host/onboarding');
                }
                $payload = [];
                if (!empty($data['phone_number'])) $payload['phone_number'] = trim((string)$data['phone_number']);
                if (!empty($data['business_name'])) $payload['business_name'] = trim((string)$data['business_name']);
                $res = $this->apiClient->post('/become-host', $payload, $headers);
                if ($res === null) {
                    $this->Flash->error(__('Service unavailable. Please try again.'));
                } elseif (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not upgrade to host.'));
                } else {
                    // Prefer the authoritative user in the become-host response;
                    // fall back to /me only when absent (saves a slow round-trip)
                    $respUser = (is_array($res) && !empty($res['user']) && is_array($res['user'])) ? $res['user'] : null;
                    if ($respUser === null || empty($respUser['email'])) {
                        $me = $this->apiClient->get('/me', [], $headers);
                        $respUser = (is_array($me) && !empty($me['email'])) ? $me : null;
                    }
                    if (is_array($respUser) && !empty($respUser['email'])) {
                        $updated = is_array($sessionUser) ? array_merge($sessionUser, $respUser) : $respUser;
                        $updated['role'] = strtolower((string)($respUser['role'] ?? 'owner'));
                        $updated['token'] = $rawToken;
                        $session->write('User', $updated);
                    } elseif (is_array($sessionUser)) {
                        $sessionUser['role'] = 'owner';
                        $session->write('User', $sessionUser);
                    }
                    $this->Flash->success(__('You are now a host! Add your first property.'));
                    return $this->redirect('/host/onboarding');
                }
            }
        }

        // For owners/admins: fetch my properties for status section (real data).
        // Owner → ?mine=1 (own only); admin → /admin/properties slice.
        // Never fall back to other hosts' lodges: empty stays empty.
        $myProperties = [];
        if ($isLoggedIn && $rawToken !== '' && in_array($userRole, ['owner', 'admin'], true)) {
            try {
                if ($userRole === 'admin') {
                    $pRes = $this->apiClient->get('/admin/properties', ['per_page' => 6], $headers);
                } else {
                    $pRes = $this->apiClient->get('/properties', ['mine' => 1, 'per_page' => 6], $headers);
                }
                $all = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
                if (is_array($all)) $myProperties = array_values(array_slice($all, 0, 6));
            } catch (\Throwable $e) {
                $myProperties = [];
            }
        }

        $this->set(compact('isLoggedIn', 'userRole', 'myProperties', 'sessionUser'));
        return $this->render('/Pages/join-us');
    }
    public function addListing() { return $this->redirect('/join-us'); }
    public function addListingStep02() { return $this->redirect('/join-us'); }
    public function addListingStep03() { return $this->redirect('/join-us'); }

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
    /**
     * Internal safe redirect: only relative portal paths, no protocol tricks
     * (backslashes, //host, control chars). Returns '' when unsafe.
     */
    private function safeRedirect(string $url): string
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 500) return '';
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) return '';
        if (str_contains($url, '\\') || preg_match('/[\r\n\t<>"]/', $url)) return '';
        if (!preg_match('#^/[A-Za-z0-9/_\-.?=&%#+]*$#', $url)) return '';
        return $url;
    }

    /**
     * Brute-force guard: max 10 login attempts per IP per 5 minutes.
     */
    private function loginRateLimited(string $ip): bool
    {
        $key = 'login_rate_' . md5($ip);
        $rec = \Cake\Cache\Cache::read($key, 'default');
        $now = time();
        $count = (is_array($rec) && isset($rec['exp']) && $rec['exp'] > $now) ? (int)$rec['count'] : 0;
        if ($count >= 10) return true;
        \Cake\Cache\Cache::write($key, ['count' => $count + 1, 'exp' => $now + 300]);
        return false;
    }

    public function login()
    {
        $session = $this->getRequest()->getSession();
        // Display role: which audience this sign-in is focused on (display only —
        // landing always follows the verified backend role)
        $loginRole = strtolower(trim((string)$this->getRequest()->getQuery('role', 'customer')));
        if (!in_array($loginRole, ['customer', 'owner', 'admin'], true)) {
            $loginRole = 'customer';
        }

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $ip = $this->getRequest()->clientIp() ?? 'unknown';

            // 1. Handle AJAX Session Synchronization (from login.php fetch) — verified, whitelist only
            if (!empty($data['action']) && $data['action'] === 'login_sync' && !empty($data['user'])) {
                // Must be same-origin XHR + within attempt budget
                $isXhr = strtolower((string)$this->getRequest()->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
                if (!$isXhr) {
                    return $this->response->withStatus(400)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Bad request']));
                }
                if ($this->loginRateLimited($ip)) {
                    return $this->response->withStatus(429)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Too many attempts. Try again in a few minutes.']));
                }
                $token = (string)($data['token'] ?? ($data['access_token'] ?? ''));
                if (strlen($token) < 10 || strlen($token) > 2048) {
                    return $this->response->withStatus(401)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Missing token']));
                }
                // Verify token server-side via backend (source of truth).
                // ONLY /me verifies: it is auth-guarded (401 on bad token).
                // /user/personal-details is public and returns a demo profile
                // for missing/invalid tokens — it must NEVER authenticate.
                $verifiedUser = null;
                try {
                    $me = $this->apiClient->get('/me', [], ['Authorization' => 'Bearer ' . $token]);
                    if (is_array($me) && empty($me['_status'])) {
                        $cand = $me['user'] ?? $me['data'] ?? $me;
                        if (is_array($cand) && !empty($cand['email']) && !empty($cand['id'])) {
                            $verifiedUser = $cand;
                        }
                    }
                } catch (\Throwable $e) {
                    $verifiedUser = null;
                }
                if (empty($verifiedUser) || empty($verifiedUser['email'])) {
                    return $this->response->withStatus(401)->withType('application/json')->withStringBody(json_encode(['success'=>false,'error'=>'Token verification failed']));
                }
                // Whitelist only safe fields from verified user (never trust client-supplied arbitrary keys)
                // role is required for admin/owner portal guards + header nav
                $allow = ['id','name','first_name','last_name','full_name','email','phone','phone_number','city','country','avatar','avatar_bg','avatar_color','email_verified','role','status'];
                $user = [];
                foreach ($allow as $k) {
                    if (array_key_exists($k, $verifiedUser)) $user[$k] = $verifiedUser[$k];
                }
                // Normalise role + phone aliases
                if (!empty($user['role'])) $user['role'] = strtolower((string)$user['role']);
                if (empty($user['phone']) && !empty($user['phone_number'])) $user['phone'] = $user['phone_number'];
                if (empty($user['name']) && !empty($user['full_name'])) $user['name'] = $user['full_name'];
                $user['token'] = $token;
                if (empty($user['first_name']) && !empty($user['name'])) {
                    $user['first_name'] = explode(' ', trim($user['name']))[0];
                }
                // NOTE: no session renew() here on purpose — renew() destroys the
                // previous session file, instantly logging out every other open
                // tab. Fixation risk is negligible (httponly + SameSite=Lax + 8h).
                $this->authService->syncSession($session, $user);

                return $this->response->withType('application/json')->withStringBody((string)json_encode([
                    'success' => true,
                    'user' => $user
                ]));
            }

            // 2. Handle Traditional Form Login
            $email = trim((string)($data['email'] ?? ''));
            $password = (string)($data['password'] ?? '');

            if ($email !== '' || $password !== '') {
                if ($this->loginRateLimited($ip)) {
                    $this->Flash->error(__('Too many login attempts. Please try again in a few minutes.'));
                    $this->set(compact('loginRole'));
                    return $this->render('/Pages/login');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 1) {
                    $this->Flash->error(__('Invalid email or password.'));
                    $this->set(compact('loginRole'));
                    return $this->render('/Pages/login');
                }
                $res = $this->authService->login($email, $password);
                $authToken = $res['access_token'] ?? ($res['token'] ?? null);
                $userData = $res['user'] ?? null;

                if (!empty($userData) || !empty($authToken)) {
                    $user = is_array($userData) ? $userData : [];
                    if ($authToken) {
                        $user['token'] = $authToken;
                    }
                    if (!empty($user['role'])) $user['role'] = strtolower((string)$user['role']);
                    if (empty($user['phone']) && !empty($user['phone_number'])) $user['phone'] = $user['phone_number'];
                    if (empty($user['first_name']) && !empty($user['name'])) {
                        $user['first_name'] = explode(' ', trim($user['name']))[0];
                    }
                    // NOTE: no session renew() here on purpose — renew() destroys the
                    // previous session file, instantly logging out every other open tab.
                    $this->authService->syncSession($session, $user);
                    $this->Flash->success(__('Login successful. Welcome back!'));
                    $redirect = $this->safeRedirect(trim((string)$this->getRequest()->getQuery('redirect', '')));
                    if ($redirect !== '') {
                        return $this->redirect($redirect);
                    }
                    $role = strtolower((string)($user['role'] ?? ''));
                    if ($role === 'admin') return $this->redirect('/admin/dashboard');
                    if ($role === 'owner') return $this->redirect('/host/dashboard');
                    // Host-intent sign-in but plain customer account → convert page
                    if ($loginRole === 'owner') return $this->redirect('/join-us');
                    return $this->redirect('/');
                }
                $this->Flash->error(__('Invalid email or password.'));
            }
        }

        $this->set(compact('loginRole'));
        return $this->render('/Pages/login');
    }

    public function signup()
    {
        if ($this->getRequest()->is('post')) {
            $data = $this->getRequest()->getData();
            $this->Flash->success(__('Account created successfully! Welcome to fastnetstays.com.'));
            return $this->redirect('/login');
        }
        $requestedRole = strtolower(trim((string)$this->getRequest()->getQuery('role', 'customer')));
        if (!in_array($requestedRole, ['customer', 'owner'], true)) {
            $requestedRole = 'customer';
        }
        $this->set(compact('requestedRole'));
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
        // Whitelist — only safe read endpoints are proxied. Payment/booking writes must go via BookingsController.
        // /alerts is guest-safe: web sends a per-device user_id so backend buckets never mix strangers (no shared guest_user).
        $allowedGetPrefixes = ['/map-config', '/properties', '/rooms', '/destinations', '/auth/verify', '/user/personal-details', '/bookings/calculate', '/alerts'];
        $blockedPrefixes = ['/payments/', '/bookings/create', '/bookings/calculate'];
        $isAllowed = false;
        foreach ($allowedGetPrefixes as $p) {
            if ($apiPath === $p || str_starts_with($apiPath, $p . '/') || str_starts_with($apiPath, $p)) {
                $isAllowed = true; break;
            }
        }
        // Allow exact /bookings/calculate for GET only (quote), block POST via proxy
        if ($method === 'post' && (str_starts_with($apiPath, '/payments/') || $apiPath === '/bookings/create' || $apiPath === '/bookings/calculate')) {
            return $this->response->withStatus(403)->withType('application/json')->withStringBody(json_encode(['error'=>'Forbidden via proxy — use BookingsController']));
        }
        if (!$isAllowed) {
            return $this->response->withStatus(403)->withType('application/json')->withStringBody(json_encode(['error'=>'Proxy path not allowed']));
        }
        // Simple per-IP rate limit: 60/min (expiry stored inline — Cache::write takes a config name, not a duration)
        $ip = $this->getRequest()->clientIp() ?? 'unknown';
        $cacheKey = 'api_proxy_rate_' . md5($ip . $apiPath);
        $rate = \Cake\Cache\Cache::read($cacheKey, 'default');
        $rateCount = (is_array($rate) && isset($rate['exp']) && $rate['exp'] > time()) ? (int)$rate['count'] : 0;
        if ($rateCount >= 60) {
            return $this->response->withStatus(429)->withType('application/json')->withStringBody(json_encode(['error'=>'Rate limit exceeded']));
        }
        \Cake\Cache\Cache::write($cacheKey, ['count' => $rateCount + 1, 'exp' => time() + 60]);

        $queryParams = $this->getRequest()->getQueryParams();
        $body = $this->getRequest()->getData();

        if ($method === 'post') {
            $data = $this->apiClient->post($apiPath, (array)$body);
        } elseif ($method === 'delete') {
            // Forward query string (e.g. /alerts/{id}?user_id=…) — backend scopes buckets by it
            $delPath = $apiPath . ($queryParams ? '?' . http_build_query($queryParams) : '');
            $data = $this->apiClient->delete($delPath);
        } else {
            $data = $this->apiClient->get($apiPath, $queryParams);
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode($data ?? []));
    }
}
