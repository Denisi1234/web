<?php
declare(strict_types=1);

namespace App\Controller\Pages;

use Cake\Core\Configure;

/**
 * PagesHomeTrait — Homepage search with spec/legacy normalization.
 */
trait PagesHomeTrait
{
    use PagesSamplesTrait;

    /**
     * Homepage - Google Hotels-style split-screen search + map
     * Reads search params from URL query string, fetches real properties, passes all to view.
     */
    public function index()
    {
        $input = $this->getRequest()->getQueryParams();
        $today = new \DateTimeImmutable('today');
        $searchErrors = [];

        // SPEC ALIASES: support both spec (?city=&checkin=&price_min=…) and legacy (?destination=&checkIn=…&min_price=…) keys.
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

        // Empty destination = all Tanzania; canonicalize case/prefix (ARUSHA/ArU
        // → Arusha) so backend q, filter, and cache agree.
        $destination = $rawCity !== null ? trim((string)$rawCity) : '';
        if (mb_strlen($destination) > 120) {
            $destination = mb_substr($destination, 0, 120);
        }
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

        // Normalized query params (spec + legacy aliases); empty destination keeps city=''.
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
            // Backend is source of truth: canonical label under every key it may read.
            $apiPayload['q'] = $destination;
            $apiPayload['city'] = $destination;
            $apiPayload['destination'] = $destination;
        }
        // Sort forwarded — backend PropertySearchService: price_asc/price_desc/rating; omitted = Recommended (reviews_avg_rating DESC)
        $sortMap = ['price_asc' => 'price_asc', 'price_desc' => 'price_desc', 'rating' => 'rating'];
        if (!empty($sortMap[$sortBy])) $apiPayload['sort'] = $sortMap[$sortBy];
        // Paginate server-side (backend max 100) — 48 fills list + map (client marker cap 60) without rendering everything
        $apiPayload['per_page'] = 48;
        // Backend owns ALL filtering — every filter is forwarded, never second-guessed.
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

        // Localhost design preview (gated inside: local hostname only).
        [$properties, $isSampleData] = $this->maybeLocalSamples($properties);
        // Real Mapbox token, server-side (no CORS race); env fallback, then free
        // Carto basemap when keyless so the map never shows "unavailable".
        $mapboxToken = null;
        $mapboxStyle = (string)Configure::read('App.mapboxStyle', 'mapbox://styles/mapbox/streets-v12');
        $osmFallbackStyle = 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json';
        try {
            // Fail-fast 2s so map config never blocks results (cached 5min).
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
        
        // Backend is source of truth — empty means empty (never demo data), results arrive already filtered.

        $totalCount = count($properties);
        // Honest counts: backend paginator total (all hits) vs shown on this page. Header shows "X of Y" when partial.
        $totalHits = $this->staysService->lastTotal;
        if ($totalHits !== null && $totalHits < $totalCount) $totalHits = $totalCount;

        // JSON hydration for FastNetState AJAX (?format=json): 30s public cache + ETag.
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
            'recentStays', 'isSampleData'
        ));
        return $this->render('/Pages/index');
    }
}
