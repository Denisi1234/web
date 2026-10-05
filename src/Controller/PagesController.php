<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\HostIntent;
use App\Service\StaysService;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\UnauthorizedException;
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

        // Destination: empty means "All Tanzanian Destinations" — list every
        // available property. Never default to a city: a guest who hasn't
        // searched yet must see the full inventory, not one city's slice.
        $destination = $rawCity !== null ? trim((string)$rawCity) : '';
        if (mb_strlen($destination) > 120) {
            $destination = mb_substr($destination, 0, 120);
        }
        // Case-insensitive + prefix-tolerant: ARUSHA / arusha / ArU / arusa → "Arusha".
        // Normalizing here guarantees backend q + local filter + cache all see the same value.
        if ($destination !== '' && strtolower($destination) !== 'tanzania') {
            $canonLabel = \App\Utility\CityAliases::resolveLabel($destination);
            if ($canonLabel !== null) {
                $destination = $canonLabel;
            }
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
            // Silent auto-correction: no "moved" notice on first open or stale URLs.
            $checkIn = $today->modify('+7 days');
        }
        $checkOut = $parseDate(is_string($rawCheckOut) ? $rawCheckOut : null);
        if (!$checkOut || $checkOut <= $checkIn) {
            $checkOut = $checkIn->modify('+1 day');
        }

        // Guests — spec: adults 1-10, children 0-6, rooms 1-5
        $adults   = max(1, min(10, (int)($input['adults']   ?? 2)));
        $childrenRaw = (int)($input['children'] ?? 0);
        if ($childrenRaw > 6) { $childrenRaw = 6; }
        $children = max(0, min(6, $childrenRaw));
        $roomsRaw = (int)($input['rooms']    ?? 1);
        if ($roomsRaw > 5) { $roomsRaw = 5; }
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
            'destination'      => $displayCity,
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
        if (!$isAllTanzania) {
            // Backend is source of truth: send the canonical label (ARUSHA/ArU
            // already normalized to Arusha above) under every key the API
            // may read (q / city / destination).
            $apiPayload['q'] = $destination;
            $apiPayload['city'] = $destination;
            $apiPayload['destination'] = $destination;
        }
        // Sort forwarded — backend PropertySearchService: price_asc/price_desc/rating; omitted = Recommended (reviews_avg_rating DESC)
        $sortMap = ['price_asc' => 'price_asc', 'price_desc' => 'price_desc', 'rating' => 'rating'];
        if (!empty($sortMap[$sortBy])) $apiPayload['sort'] = $sortMap[$sortBy];
        // Paginate server-side (backend max 100) — 48 fills list + map (client marker cap 60) without rendering everything
        $apiPayload['per_page'] = 48;
        // Backend owns ALL filtering (city, price, rating, amenities, type).
        // Every filter is forwarded — the frontend never second-guesses results.
        if ($propertyType !== '') $apiPayload['property_type'] = $propertyType;
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
            // Fail-fast 2s: map token must never block search results (was 10s default).
            // Cached 5min in FastnetApiClient; env fallback covers cold miss.
            $cfg = $this->apiClient->get('/map-config', [], [], 2);
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
        
        // Backend is source of truth — empty backend means empty results
        // ("No stays found"). No demo/mock data is ever injected.

        // Backend is source of truth — results arrive already filtered
        // (city, price, rating, amenities, type). No local post-filtering.

        $totalCount = count($properties);
        // Honest counts: backend paginator total (all hits) vs shown on this page. Header shows "X of Y" when partial.
        $totalHits = $this->staysService->lastTotal;
        if ($totalHits !== null && $totalHits < $totalCount) $totalHits = $totalCount;

        // JSON hydration for FastNetState AJAX — ?format=json
        // 30s public cache + ETag: CDN/browser serves repeats instantly, revalidates after.
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
            $body = (string)json_encode($payload);
            $etag = '"' . md5($body) . '"';
            if (trim((string)$this->getRequest()->getHeaderLine('If-None-Match')) === $etag) {
                return $this->response->withStatus(304);
            }
            return $this->response
                ->withType('application/json')
                ->withHeader('Cache-Control', 'public, max-age=30, stale-while-revalidate=60')
                ->withHeader('ETag', $etag)
                ->withStringBody($body);
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
        if ($rawToken === '') {
            $rawToken = $this->authService->readToken($this->getRequest());
        }
        $isLoggedIn = !$session->read('is_logged_out') && !empty($sessionUser);
        $userRole = strtolower((string)($sessionUser['role'] ?? ''));
        $headers = $rawToken !== '' ? ['Authorization' => 'Bearer ' . $rawToken] : [];

        // A guest account is NOT convertible to a host account. This page used
        // to POST action=become_host and upgrade the signed-in customer's role
        // to owner. That is gone: hosting is a separate account with its own
        // sign-in, reached through /signup?role=owner. The branch is rejected
        // rather than ignored so a stale bookmarked form cannot still upgrade.
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            if (($data['action'] ?? '') === 'become_host') {
                $this->Flash->error(__(
                    'A guest account cannot be turned into a host account. '
                    . 'Please register a separate host account.'
                ));
                return $this->redirect('/signup?role=owner');
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
    /**
     * Owner KYC submission — POST /verification/owner.
     *
     * Uploads any identity/business documents to POST /upload first (the
     * endpoint stores the file and returns a public URL), then submits those
     * URLs with the rest of the payload. Without this an upgraded host is
     * stuck at "Pending Verification" with no way to submit documents.
     */
    public function submitOwnerVerification(): Response
    {
        if (!$this->getRequest()->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $token = $this->currentBearerToken();
        if ($token === '') {
            throw new UnauthorizedException(__('Sign in to submit verification.'));
        }
        $headers = ['Authorization' => 'Bearer ' . $token];

        $uploadOne = function (?string $field) use ($headers): ?string {
            $file = $this->getRequest()->getUploadedFile($field);
            if ($file === null) {
                return null;
            }
            if ($file->getError() !== UPLOAD_ERR_OK) {
                throw new BadRequestException(__('Could not read the uploaded file. Please try again.'));
            }
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new BadRequestException(__('Documents must be 10 MB or smaller.'));
            }

            $tmp = $file->getStream()->getMetadata('uri');
            if (!is_string($tmp) || !is_readable($tmp)) {
                throw new BadRequestException(__('Could not read the uploaded file. Please try again.'));
            }

            $res = $this->apiClient->uploadFile(
                '/upload',
                'file',
                $tmp,
                $file->getClientFilename(),
                $file->getClientMediaType() ?? 'application/octet-stream',
                $headers
            );

            if (empty($res) || !empty($res['_status']) || empty($res['url'])) {
                throw new BadRequestException(__('Document upload failed. Please try again.'));
            }

            return (string)$res['url'];
        };

        $idDocUrl = $uploadOne('id_document');
        if ($idDocUrl === null) {
            throw new BadRequestException(__('A photo of your National ID is required.'));
        }

        $payload = [
            'full_name'       => trim((string)$this->getRequest()->getData('full_name')),
            'phone_number'    => trim((string)$this->getRequest()->getData('phone_number')),
            'id_number'       => trim((string)$this->getRequest()->getData('id_number')),
            'id_document_url' => $idDocUrl,
        ];

        foreach (['business_registration_number', 'payout_bank_name', 'payout_account_number', 'payout_account_name'] as $opt) {
            $v = trim((string)$this->getRequest()->getData($opt));
            if ($v !== '') {
                $payload[$opt] = $v;
            }
        }

        $businessDocUrl = $uploadOne('business_document');
        if ($businessDocUrl !== null) {
            $payload['business_document_url'] = $businessDocUrl;
        }

        $res = $this->apiClient->post('/verification/owner', $payload, $headers);

        if (empty($res) || !empty($res['_status'])) {
            $msg = $res['message'] ?? __('Could not submit verification. Please try again.');
            $this->Flash->error(__($msg));

            if ($this->getRequest()->is('json')) {
                return $this->response
                    ->withStatus(!empty($res['_status']) ? (int)$res['_status'] : 502)
                    ->withType('application/json')
                    ->withStringBody(json_encode(['status' => 'error', 'message' => $msg]));
            }

            return $this->redirect('/join-us');
        }

        $this->Flash->success(__('Documents submitted. We will review them shortly.'));

        if ($this->getRequest()->is('json')) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'status'  => 'success',
                'message' => __('Documents submitted. We will review them shortly.'),
            ]));
        }

        return $this->redirect('/host/onboarding');
    }

    /** Current bearer token from session or persistent cookie. */
    private function currentBearerToken(): string
    {
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '') {
            $token = trim((string)$this->authService->readToken($this->getRequest()));
            if (stripos($token, 'Bearer ') === 0) {
                $token = trim(substr($token, 7));
            }
        }

        return $token;
    }

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

                // Persistent login cookie — auth survives session loss
                // (multi-instance file sessions, browser restarts).
                $loginResp = $this->response->withType('application/json')->withStringBody((string)json_encode([
                    'success' => true,
                    'user' => $user
                ]));
                return $this->authService->persistToken($loginResp, $token);
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
                    // Persistent login cookie — portal auth no longer depends
                    // on the PHP session file surviving.
                    $persist = function (\Cake\Http\Response $resp) use ($authToken): \Cake\Http\Response {
                        return $this->authService->persistToken($resp, (string)$authToken);
                    };
                    $redirect = $this->safeRedirect(trim((string)$this->getRequest()->getQuery('redirect', '')));
                    if ($redirect !== '') {
                        return $persist($this->redirect($redirect));
                    }
                    $role = strtolower((string)($user['role'] ?? ''));
                    if ($role === 'admin') return $persist($this->redirect('/admin/dashboard'));
                    if ($role === 'owner') return $persist($this->redirect('/host/dashboard'));
                    // Host-intent sign-in but plain customer account → convert page
                    if ($loginRole === 'owner') return $persist($this->redirect('/join-us'));
                    return $persist($this->redirect('/'));
                }
                $this->Flash->error(__('Invalid email or password.'));
            }
        }

        $this->set(compact('loginRole'));
        return $this->render('/Pages/login');
    }

    public function signup()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // Resolve whether this is a host or guest signup, carrying intent in
        // from an explicit ?role=, a remembered choice, or the referring page.
        $requestedRole = HostIntent::resolve($request, $session->read('signup_intent_role'));
        $session->write('signup_intent_role', $requestedRole);

        if ($request->is('post')) {
            // The browser posts the real form to the backend via
            // /api/register. This fallback only runs when that client-side
            // path is bypassed. It used to flash "Account created
            // successfully!" and redirect to /login without creating anything,
            // so a failed registration looked like a success.
            $this->Flash->error(__(
                'Please complete registration in the form above. '
                . 'If it keeps failing, try again in a moment.'
            ));
            return $this->redirect($requestedRole === HostIntent::ROLE_OWNER
                ? '/signup?role=owner'
                : '/signup');
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
    public function helpCenter()
    {
        // Real help-centre feed: popular topics (with real action URLs),
        // support contact from backend config, and the signed-in guest's
        // upcoming stay. Public endpoint — works logged out too. Never
        // fabricated: on backend failure the page renders contact + FAQs.
        $helpCentre = null;
        try {
            $token = $this->currentBearerToken();
            $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
            $res = $this->apiClient->get('/support/help-centre', [], $headers, 6);
            if (is_array($res) && ($res['status'] ?? '') === 'success' && empty($res['_status'])) {
                $helpCentre = $res;
            }
        } catch (\Throwable $e) {
            $helpCentre = null;
        }
        $session = $this->getRequest()->getSession();
        $isLoggedIn = $this->authService->isAuthenticated($session);
        $this->set(compact('helpCentre', 'isLoggedIn'));
        return $this->render('/Pages/help-center');
    }
    public function faq()
    {
        // Support email only — office addresses and phone numbers on the old
        // page were unverified, so they are not rendered anymore.
        $supportEmail = '';
        try {
            $res = $this->apiClient->get('/support/help-centre', [], [], 6);
            if (is_array($res) && ($res['status'] ?? '') === 'success') {
                $supportEmail = trim((string)($res['support_contact']['email'] ?? ''));
            }
        } catch (\Throwable $e) {
            $supportEmail = '';
        }
        $this->set(compact('supportEmail'));
        return $this->render('/Pages/faq');
    }
    public function notFound() { return $this->render('/Pages/404'); }
    public function privacyPolicy() { return $this->render('/Pages/privacy-policy'); }
    public function termsOfService() { return $this->render('/Pages/terms-of-service'); }
    public function contactV1()
    {
        // Real support contact from backend config (email only — no invented
        // phone numbers). Ticket submission needs auth server-side, so guests
        // get the direct email path instead of a form that can never send.
        $supportEmail = '';
        try {
            $res = $this->apiClient->get('/support/help-centre', [], [], 6);
            if (is_array($res) && ($res['status'] ?? '') === 'success') {
                $supportEmail = trim((string)($res['support_contact']['email'] ?? ''));
            }
        } catch (\Throwable $e) {
            $supportEmail = '';
        }
        $session = $this->getRequest()->getSession();
        $isLoggedIn = $this->authService->isAuthenticated($session);
        $this->set(compact('supportEmail', 'isLoggedIn'));
        return $this->render('/Pages/contact-v1');
    }

    /**
     * Strict allowlist match for the API proxy: the exact path or a real
     * sub-path only. "/properties" matches "/properties/5" but never
     * "/properties-evil" (the old loose prefix check allowed those).
     */
    private function proxyPathMatches(string $apiPath, string $prefix): bool
    {
        if ($apiPath === $prefix) return true;
        return str_starts_with($apiPath, $prefix . '/');
    }

    // ── Universal API Proxy (localhost & production) ─────────────────────
    public function apiProxy(string ...$path): Response
    {
        $apiPath = '/' . implode('/', $path);
        $method = strtolower($this->getRequest()->getMethod());

        // Reject traversal / encoding tricks outright — the proxy must only
        // ever forward clean sub-paths of the allowlisted resources.
        $lowerPath = strtolower($apiPath);
        if (
            $apiPath === '' || $apiPath === '/' ||
            str_contains($apiPath, '..') || str_contains($apiPath, '\\') ||
            str_contains($apiPath, "\0") || str_contains($lowerPath, '%2e') ||
            str_contains($lowerPath, '%00') || str_contains($lowerPath, '%5c')
        ) {
            return $this->response->withStatus(403)->withType('application/json')->withStringBody(json_encode(['error' => 'Proxy path not allowed']));
        }

        $allowedGetPrefixes = [
            '/map-config', '/properties', '/rooms', '/destinations',
            '/auth/verify', '/user/personal-details', '/bookings/calculate',
            '/alerts', '/search/suggestions', '/currencies',
            '/notifications/preferences', '/travel/preferences', '/support/help-centre'
        ];
        $allowedPostPrefixes = [
            '/notifications/preferences', '/travel/preferences',
            '/receipts/generate', '/feedback/accessibility',
            '/user/personal-details', '/alerts',
            // AzamPay transaction callback lands here in production
            // (callback URL is https://fastnetstays.com/api/payments/webhook).
            // The backend owns validation (no success default, amount match
            // enforced), so forwarding the payload is safe.
            '/payments/webhook',
        ];

        // Exactly one property POST: the AI description draft. Scoped to a
        // suffix rather than opening all of POST /properties, and the backend
        // enforces that the caller owns the property.
        $isGenerateDescription = (bool) preg_match(
            '#^/properties/\d+/generate-description$#',
            $apiPath
        );
        $allowedDeletePrefixes = ['/alerts', '/wishlist'];

        $isAllowed = false;
        if ($method === 'get') {
            foreach ($allowedGetPrefixes as $p) {
                if ($this->proxyPathMatches($apiPath, $p)) {
                    $isAllowed = true; break;
                }
            }
        } elseif ($method === 'post') {
            if ($isGenerateDescription) {
                $isAllowed = true;
            }
            foreach ($allowedPostPrefixes as $p) {
                if ($this->proxyPathMatches($apiPath, $p)) {
                    $isAllowed = true; break;
                }
            }
        } elseif ($method === 'delete') {
            foreach ($allowedDeletePrefixes as $p) {
                if ($this->proxyPathMatches($apiPath, $p)) {
                    $isAllowed = true; break;
                }
            }
        }

        if (!$isAllowed) {
            return $this->response->withStatus(403)->withType('application/json')->withStringBody(json_encode(['error' => 'Proxy path not allowed']));
        }

        // Simple per-IP rate limit: 120/min
        $ip = $this->getRequest()->clientIp() ?? 'unknown';
        $cacheKey = 'api_proxy_rate_' . md5($ip . $apiPath);
        $rate = \Cake\Cache\Cache::read($cacheKey, 'default');
        $rateCount = (is_array($rate) && isset($rate['exp']) && $rate['exp'] > time()) ? (int)$rate['count'] : 0;
        if ($rateCount >= 120) {
            return $this->response->withStatus(429)->withType('application/json')->withStringBody(json_encode(['error' => 'Rate limit exceeded']));
        }
        \Cake\Cache\Cache::write($cacheKey, ['count' => $rateCount + 1, 'exp' => time() + 60]);

        $token = $this->authService->readToken($this->getRequest());
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        $queryParams = $this->getRequest()->getQueryParams();
        $body = $this->getRequest()->getData();

        if ($method === 'post') {
            $data = $this->apiClient->post($apiPath, (array)$body, $headers);
        } elseif ($method === 'delete') {
            $delPath = $apiPath . ($queryParams ? '?' . http_build_query($queryParams) : '');
            $data = $this->apiClient->delete($delPath, $headers);
        } else {
            $data = $this->apiClient->get($apiPath, $queryParams, $headers);
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode($data ?? []));
    }
}
