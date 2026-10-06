<?php
declare(strict_types=1);

namespace App\Service\Stays;

use Cake\Cache\Cache;

/**
 * StaysFetchTrait — Property, room, review, and count fetches.
 */
trait StaysFetchTrait
{
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
