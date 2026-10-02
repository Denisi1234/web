<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;

/**
 * PortalService
 *
 * Backend-as-source-of-truth reads for the admin/owner portal with
 * short-TTL shared caching. Portal pages previously made 2-4 sequential
 * backend HTTP calls per page view (dashboard = properties + bookings +
 * users ≈ seconds); repeats now serve from Redis/File in ~1ms.
 *
 * Error responses (null / 4xx / 5xx) are NEVER cached. Call clear() after
 * any successful write so redirects never render stale lists.
 */
class PortalService
{
    protected FastnetApiClient $api;

    /** Per-request memo — duplicate reads inside one page load cost 0ms. */
    private static array $memo = [];

    private const REGISTRY_KEY = 'portal_cache_keys';
    private const REGISTRY_MAX = 200;

    public function __construct(?FastnetApiClient $apiClient = null)
    {
        $this->api = $apiClient ?: new FastnetApiClient();
    }

    /**
     * Cached backend GET. Returns the raw response array (same shape as
     * FastnetApiClient::get) so existing extraction code works unchanged.
     */
    public function get(string $endpoint, array $params = [], array $headers = [], int $ttl = 60): ?array
    {
        $cacheKey = 'portal_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $endpoint) . '_' . md5($endpoint . '|' . (string)json_encode($this->normalize($params)) . '|' . md5((string)json_encode($headers)));

        if (isset(self::$memo[$cacheKey])) {
            $hit = self::$memo[$cacheKey];
            if ($hit['exp'] > time()) {
                return $hit['data'];
            }
            unset(self::$memo[$cacheKey]);
        }

        try {
            $cached = Cache::read($cacheKey, 'default');
            if (is_array($cached) && isset($cached['exp'], $cached['data']) && $cached['exp'] > time()) {
                self::$memo[$cacheKey] = $cached;
                return $cached['data'];
            }
        } catch (\Throwable $e) {
        }

        $res = $this->api->get($endpoint, $params, $headers, 6);
        if ($res === null || (!empty($res['_status']) && (int)$res['_status'] >= 400)) {
            return $res;
        }

        $entry = ['exp' => time() + max(5, $ttl), 'data' => $res];
        self::$memo[$cacheKey] = $entry;
        try {
            Cache::write($cacheKey, $entry, 'default');
            $this->register($cacheKey);
        } catch (\Throwable $e) {
        }

        return $res;
    }

    /**
     * Invalidate portal reads. Pass a scope (or list, e.g. ['rooms', 'properties'])
     * to bust only matching entries so an unrelated write doesn't cold every
     * portal page. Null/empty clears everything (previous behavior).
     * Scopes ignore leading underscores and -/_ differences, so '_lodge_requests'
     * matches '/lodge-requests/…' keys and 'rooms' matches '/rooms/54' as well
     * as '/properties/5/rooms'.
     */
    public function clear(string|array|null $match = null): void
    {
        $scopes = $match === null ? [] : array_values(array_filter(array_map('strval', (array)$match)));
        $matchAll = $scopes === [];
        $hits = function (string $key) use ($scopes, $matchAll): bool {
            if ($matchAll) return true;
            $k = strtolower($key);
            foreach ($scopes as $s) {
                $s = strtolower(ltrim(trim($s), '_'));
                if ($s === '') return true;
                if (str_contains($k, $s)) return true;
                $dash = str_replace('_', '-', $s);
                if ($dash !== $s && str_contains($k, $dash)) return true;
                $under = str_replace('-', '_', $s);
                if ($under !== $s && str_contains($k, $under)) return true;
            }
            return false;
        };
        if ($matchAll) {
            self::$memo = [];
        } else {
            foreach (self::$memo as $k => $v) {
                if ($hits((string)$k)) unset(self::$memo[$k]);
            }
        }
        
        try {
            $keys = Cache::read(self::REGISTRY_KEY, 'default');
            if (is_array($keys)) {
                $retained = [];
                foreach ($keys as $k) {
                    if ($hits((string)$k)) {
                        try {
                            Cache::delete((string)$k, 'default');
                        } catch (\Throwable $e) {}
                    } else {
                        $retained[] = $k;
                    }
                }
                if ($matchAll) {
                    Cache::delete(self::REGISTRY_KEY, 'default');
                } else {
                    Cache::write(self::REGISTRY_KEY, $retained, 'default');
                }
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Stable cache key: sorted params, empty values dropped.
     */
    private function normalize(array $params): array
    {
        $norm = [];
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) {
                continue;
            }
            $norm[(string)$k] = is_string($v) ? trim($v) : $v;
        }
        ksort($norm);
        return $norm;
    }

    private function register(string $cacheKey): void
    {
        try {
            $keys = Cache::read(self::REGISTRY_KEY, 'default');
            if (!is_array($keys)) {
                $keys = [];
            }
            if (!in_array($cacheKey, $keys, true)) {
                $keys[] = $cacheKey;
                $keys = array_slice($keys, -self::REGISTRY_MAX);
                Cache::write(self::REGISTRY_KEY, $keys, 'default');
            }
        } catch (\Throwable $e) {
        }
    }
}
