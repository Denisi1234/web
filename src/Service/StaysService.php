<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Stays\StaysFetchTrait;
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

    use StaysFetchTrait;

    /** Search results change with availability — short TTL for instant repeat searches. */
    private const SEARCH_TTL = 90;

    /** Property detail is fairly static — longer TTL, invalidated on writes via PortalService::clear(). */
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
}
