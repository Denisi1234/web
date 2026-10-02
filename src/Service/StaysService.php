<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;

/**
 * StaysService
 * 
 * Modular domain service handling accommodation/hotel stays,
 * regional aggregations, search filtering, and property details.
 */
class StaysService
{
    protected FastnetApiClient $apiClient;

    /** Total hits from last paginated search (Laravel paginator `total`), null when backend omits it. */
    public ?int $lastTotal = null;

    /** Per-request memo so repeated searches in one page load (e.g. breadcrumb count) cost 0ms. */
    private static array $memo = [];

    /** Search results change with availability — short TTL for instant repeat searches. */
    private const SEARCH_TTL = 90;

    /** Property detail is fairly static — longer TTL, invalidated on writes via clearPropertiesCache. */
    private const PROPERTY_TTL = 300;

    /** Rooms change rarely — 120s. Reviews change slowly — 300s. City counts — 600s. */
    private const ROOMS_TTL = 120;
    private const REVIEWS_TTL = 300;
    private const CITY_COUNT_TTL = 600;

    public function __construct(?FastnetApiClient $apiClient = null)
    {
        $this->apiClient = $apiClient ?: new FastnetApiClient();
    }

    /**
     * Get featured stays and luxury resorts for homepage
     */
    public function getFeaturedResorts(int $limit = 50): array
    {
        $res = $this->apiClient->get('/properties', ['limit' => $limit]);
        if (empty($res) || (!empty($res['_status']) && $res['_status'] >= 400)) {
            return [];
        }
        $data = $res['data'] ?? ($res['items'] ?? $res);
        if (is_array($data) && !empty($data)) {
            // Filter to only valid property arrays
            $filtered = array_filter($data, fn($item) => is_array($item) && isset($item['id']));
            return $filtered ? array_values($filtered) : [];
        }

        return [];
    }

    /**
     * Build the 4 requested regions with real live property counts
     */
    public function getDestinationsSummary(array $properties = []): array
    {
        if (empty($properties)) {
            $properties = $this->getFeaturedResorts(50);
        }

        $regions = [
            [
                'title' => 'Zanzibar',
                'city' => 'Zanzibar',
                'keywords' => ['zanzibar', 'znz', 'nungwi', 'paje', 'stone town'],
                'img' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80'
            ],
            [
                'title' => 'Dar es Salaam',
                'city' => 'Dar es Salaam',
                'keywords' => ['dar', 'dar es salaam', 'dar es salam', 'mbezi', 'kinondoni', 'masaki'],
                'img' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=600&q=80'
            ],
            [
                'title' => 'Arusha',
                'city' => 'Arusha',
                'keywords' => ['arusha', 'sekei', 'ngorongoro'],
                'img' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80'
            ],
            [
                'title' => 'Dodoma',
                'city' => 'Dodoma',
                'keywords' => ['dodoma', 'capital'],
                'img' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=600&q=80'
            ]
        ];

        foreach ($regions as &$reg) {
            $count = 0;
            if (is_array($properties)) {
                foreach ($properties as $p) {
                    $pSearch = strtolower(trim(($p['city'] ?? '') . ' ' . ($p['area'] ?? '') . ' ' . ($p['name'] ?? '')));
                    foreach ($reg['keywords'] as $kw) {
                        if (str_contains($pSearch, $kw)) {
                            $count++;
                            break;
                        }
                    }
                }
            }
            $reg['count'] = max(1, $count);
        }

        return $regions;
    }

    /**
     * Search properties by city, dates, guests, or price
     * Ultra-fast path: 90s shared cache (Redis/File) + per-request memo.
     * Repeat searches (typing, back/forward, filters) return in ~1-5ms
     * instead of 200-1000ms backend HTTP round-trip.
     */
    public function searchProperties(array $params = []): array
    {
        $norm = $this->normalizeSearchParams($params);
        $cacheKey = 'stays_search_' . md5((string)json_encode($norm));

        // 1. Per-request memo — 0ms for duplicate calls in same page (e.g. breadcrumb count).
        if (isset(self::$memo[$cacheKey])) {
            $hit = self::$memo[$cacheKey];
            if ($hit['exp'] > time()) {
                $this->lastTotal = $hit['total'];
                return $hit['data'];
            }
            unset(self::$memo[$cacheKey]);
        }

        // 2. Shared cache — instant across requests/servers when Redis is configured.
        try {
            $cached = Cache::read($cacheKey, 'default');
            if (is_array($cached) && isset($cached['exp'], $cached['data']) && $cached['exp'] > time()) {
                $this->lastTotal = $cached['total'] ?? null;
                self::$memo[$cacheKey] = $cached;
                return $cached['data'];
            }
        } catch (\Throwable $e) {
            // Cache backend down — fall through to live fetch.
        }

        $res = $this->apiClient->get('/properties', $params, [], 4);
        if ($res === null || (!empty($res['_status']) && $res['_status'] >= 400)) {
            $this->lastTotal = null;
            return [];
        }

        // Laravel paginator shape {data, total, per_page} — capture total hits for honest counts
        if (isset($res['total']) && isset($res['data']) && is_array($res['data'])) {
            $this->lastTotal = (int)$res['total'];
        } elseif (isset($res['meta']['total'])) {
            $this->lastTotal = (int)$res['meta']['total'];
        } else {
            $this->lastTotal = null;
        }

        $data = $res['data'] ?? ($res['items'] ?? $res);
        // Ensure data is list of property arrays, not error string
        if (!is_array($data) || (isset($data['message']) && isset($data['_status']))) {
            return [];
        }
        // Filter to only array items with id
        $filtered = array_filter($data, fn($item) => is_array($item) && isset($item['id']));
        $out = $filtered ? array_values($filtered) : (is_array($data) && isset($data[0]) && is_array($data[0]) ? array_values($data) : []);

        // 3. Store for next identical search — 90s TTL enforced inline
        // (works on File + Redis without needing per-key duration support).
        $entry = ['exp' => time() + self::SEARCH_TTL, 'data' => $out, 'total' => $this->lastTotal];
        self::$memo[$cacheKey] = $entry;
        try {
            Cache::write($cacheKey, $entry, 'default');
        } catch (\Throwable $e) {
        }

        return $out;
    }

    /**
     * Normalize search params into a stable cache key.
     * Lowercases city/q, sorts keys, drops empty values so
     * ?city=Arusha and ?city=arusha&sort= share one entry.
     */
    private function normalizeSearchParams(array $params): array
    {
        $norm = [];
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) {
                continue;
            }
            $norm[(string)$k] = is_string($v) && in_array((string)$k, ['q', 'city', 'destination', 'sort'], true)
                ? mb_strtolower(trim($v))
                : (is_string($v) ? trim($v) : $v);
        }
        ksort($norm);
        return $norm;
    }

    /**
     * Get property by ID, forwarding search context so the backend can attach
     * per-room availability (is_available, meets_capacity, unavailability_reason).
     */
    public function getProperty(int $id, array $params = []): ?array
    {
        // Availability varies by dates — only cache bare detail (no date params).
        $cacheable = empty($params['check_in']) && empty($params['check_out']) && empty($params['guests']);
        $pKey = 'stays_prop_' . $id;
        if ($cacheable) {
            if (isset(self::$memo[$pKey]) && self::$memo[$pKey]['exp'] > time()) {
                return self::$memo[$pKey]['data'];
            }
            try {
                $hit = Cache::read($pKey, 'default');
                if (is_array($hit) && isset($hit['exp'], $hit['data']) && $hit['exp'] > time()) {
                    self::$memo[$pKey] = $hit;
                    return $hit['data'];
                }
            } catch (\Throwable $e) {
            }
        }

        $res = $this->apiClient->get('/properties/' . $id, $params, [], 4);
        if (!empty($res['_status']) && $res['_status'] >= 400) {
            $res = null;
        }
        if (!empty($res['data']) && is_array($res['data'])) {
            $prop = $res['data'];
            if ($cacheable) {
                $entry = ['exp' => time() + self::PROPERTY_TTL, 'data' => $prop];
                self::$memo[$pKey] = $entry;
                try { Cache::write($pKey, $entry, 'default'); } catch (\Throwable $e) {}
            }
            return $prop;
        }
        if (!empty($res) && is_array($res) && isset($res['id'])) {
            if ($cacheable) {
                $entry = ['exp' => time() + self::PROPERTY_TTL, 'data' => $res];
                self::$memo[$pKey] = $entry;
                try { Cache::write($pKey, $entry, 'default'); } catch (\Throwable $e) {}
            }
            return $res;
        }

        // Some backend deployments expose the collection endpoint reliably
        // while the single-property route is unavailable. Search that same
        // live backend response before treating the property as missing.
        $collection = $this->apiClient->get('/properties', ['limit' => 100]);
        if (!empty($collection['_status']) && $collection['_status'] >= 400) {
            return null;
        }
        $properties = $collection['data'] ?? ($collection['items'] ?? $collection);
        if (is_array($properties)) {
            foreach ($properties as $property) {
                if (is_array($property) && (int)($property['id'] ?? 0) === $id) {
                    return $property;
                }
            }
        }

        return null;
    }

    /**
     * Get rooms for a property — cached 120s. This was a blocking backend
     * round-trip on every detail page view; now repeats are ~1ms.
     */
    public function getRooms(int $propertyId, array $params = []): array
    {
        // Availability varies by dates — include dates in key, short TTL still helps.
        $keyParams = $params;
        unset($keyParams['destination'], $keyParams['city'], $keyParams['format']);
        $cacheKey = 'stays_rooms_' . $propertyId . '_' . md5((string)json_encode($keyParams));
        $ttl = !empty($params['checkIn']) || !empty($params['check_in']) ? 60 : self::ROOMS_TTL;

        if (isset(self::$memo[$cacheKey]) && self::$memo[$cacheKey]['exp'] > time()) {
            return self::$memo[$cacheKey]['data'];
        }
        try {
            $hit = Cache::read($cacheKey, 'default');
            if (is_array($hit) && isset($hit['exp'], $hit['data']) && $hit['exp'] > time()) {
                self::$memo[$cacheKey] = $hit;
                return $hit['data'];
            }
        } catch (\Throwable $e) {
        }

        $res = $this->apiClient->get('/properties/' . $propertyId . '/rooms', $params, [], 4);
        $rooms = [];
        if (is_array($res)) {
            $rooms = $res['data'] ?? ($res['items'] ?? $res);
            if (isset($rooms['message']) && isset($rooms['_status'])) {
                $rooms = [];
            }
        }
        if (!is_array($rooms)) {
            $rooms = [];
        }
        $rooms = array_values(array_filter($rooms, fn($r) => is_array($r)));

        $entry = ['exp' => time() + $ttl, 'data' => $rooms];
        self::$memo[$cacheKey] = $entry;
        try { Cache::write($cacheKey, $entry, 'default'); } catch (\Throwable $e) {}

        return $rooms;
    }

    /**
     * Get reviews for a property — cached 300s.
     */
    public function getReviews(int $propertyId, int $page = 1): array
    {
        $cacheKey = 'stays_reviews_' . $propertyId . '_p' . max(1, $page);
        if (isset(self::$memo[$cacheKey]) && self::$memo[$cacheKey]['exp'] > time()) {
            return self::$memo[$cacheKey]['data'];
        }
        try {
            $hit = Cache::read($cacheKey, 'default');
            if (is_array($hit) && isset($hit['exp'], $hit['data']) && $hit['exp'] > time()) {
                self::$memo[$cacheKey] = $hit;
                return $hit['data'];
            }
        } catch (\Throwable $e) {
        }

        $res = $this->apiClient->get('/properties/' . $propertyId . '/reviews', ['page' => $page], [], 4);
        $reviews = [];
        if (is_array($res)) {
            $reviews = $res['data'] ?? ($res['items'] ?? $res);
        }
        if (!is_array($reviews)) {
            $reviews = [];
        }
        $reviews = array_values(array_filter($reviews, fn($r) => is_array($r)));

        $entry = ['exp' => time() + self::REVIEWS_TTL, 'data' => $reviews];
        self::$memo[$cacheKey] = $entry;
        try { Cache::write($cacheKey, $entry, 'default'); } catch (\Throwable $e) {}

        return $reviews;
    }

    /**
     * City property count for breadcrumbs — cached 600s (counts change slowly).
     * Replaces a full searchProperties call that previously ran per detail view.
     */
    public function getCityCount(string $city): ?int
    {
        $city = trim($city);
        if ($city === '') {
            return null;
        }
        $label = \App\Utility\CityAliases::resolveLabel($city) ?? $city;
        $cacheKey = 'stays_citycount_' . md5(mb_strtolower($label));

        if (isset(self::$memo[$cacheKey]) && self::$memo[$cacheKey]['exp'] > time()) {
            $this->lastTotal = self::$memo[$cacheKey]['data'];
            return $this->lastTotal;
        }
        try {
            $hit = Cache::read($cacheKey, 'default');
            if (is_array($hit) && isset($hit['exp']) && $hit['exp'] > time()) {
                self::$memo[$cacheKey] = $hit;
                $this->lastTotal = $hit['data'];
                return $this->lastTotal;
            }
        } catch (\Throwable $e) {
        }

        $this->searchProperties(['q' => $label, 'per_page' => 1]);
        $total = $this->lastTotal;
        $entry = ['exp' => time() + self::CITY_COUNT_TTL, 'data' => $total, 'total' => $total];
        self::$memo[$cacheKey] = $entry;
        try { Cache::write($cacheKey, $entry, 'default'); } catch (\Throwable $e) {}

        return $total;
    }

    /**
     * Clear in-request memo (called after writes).
     */
    public static function clearMemo(): void
    {
        self::$memo = [];
    }
}
