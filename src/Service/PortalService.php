<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;

/**
 * PortalService
 *
 * Backend-as-source-of-truth reads for the admin/owner portal with
 * stale-while-revalidate shared caching.
 *
 * Design goal: every portal action feels ~1ms.
 *  - Per-request memo: duplicate reads inside one page load cost 0ms.
 *  - Shared cache (Redis/File): warm repeats serve in ~1ms, no backend I/O.
 *  - Stale-while-revalidate: an expired entry still serves instantly
 *    (stale window 10min) while a background refresh updates the cache
 *    after the response flushes — slow backends never block rendering.
 *  - Fail-fast backend: 2.5s single-attempt reads. A dead backend returns
 *    stale/empty in ~1ms instead of hanging the worker for 6-8s.
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
    private const REGISTRY_MAX = 500;

    /** Serve expired entries this long while refreshing in background. */
    private const STALE_WINDOW = 600;

    /** Fail-fast: portal reads never wait longer than this per call. */
    private const FAST_TIMEOUT = 3;

    public function __construct(?FastnetApiClient $apiClient = null)
    {
        $this->api = $apiClient ?: new FastnetApiClient();
    }

    /**
     * Cached backend GET. Returns the raw response array (same shape as
     * FastnetApiClient::get) so existing extraction code works unchanged.
     *
     * Cold miss = one fail-fast backend call. Warm/stale = ~1ms, no I/O.
     */
    public function get(string $endpoint, array $params = [], array $headers = [], int $ttl = 60): ?array
    {
        $cacheKey = $this->keyFor($endpoint, $params, $headers);
        $now = time();

        if (isset(self::$memo[$cacheKey])) {
            $hit = self::$memo[$cacheKey];
            if (($hit['exp'] ?? 0) > $now) {
                return $hit['data'];
            }
            // Expired memo but within stale window: serve instantly, refresh behind.
            if (($hit['stale_until'] ?? 0) > $now && !empty($hit['data'])) {
                $this->refreshInBackground($endpoint, $params, $headers, $ttl, $cacheKey);
                return $hit['data'];
            }
            unset(self::$memo[$cacheKey]);
        }

        try {
            $cached = Cache::read($cacheKey, 'default');
            if (is_array($cached) && isset($cached['exp'], $cached['data'])) {
                if ($cached['exp'] > $now) {
                    self::$memo[$cacheKey] = $cached;
                    return $cached['data'];
                }
                $staleUntil = (int)($cached['stale_until'] ?? ($cached['exp'] + self::STALE_WINDOW));
                if ($staleUntil > $now && !empty($cached['data'])) {
                    self::$memo[$cacheKey] = $cached;
                    $this->refreshInBackground($endpoint, $params, $headers, $ttl, $cacheKey);
                    return $cached['data'];
                }
            }
        } catch (\Throwable $e) {
        }

        $res = $this->api->get($endpoint, $params, $headers, self::FAST_TIMEOUT);
        if ($res === null) {
            // Transport dead: last-good stale (even beyond window) beats blank.
            $stale = $this->readStale($cacheKey);
            return $stale ?? $res;
        }
        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            // 4xx/5xx never cached; but serve stale alongside so pages still paint.
            // Callers check _status for bounce handling — preserve it via wrapper?
            // We return the error as-is (caller already handles 401), falling
            // back to stale only when there is no usable error body.
            if ($res !== null && isset($res['_status'])) {
                return $res;
            }
            return $this->readStale($cacheKey) ?? $res;
        }

        $this->store($cacheKey, $res, $ttl);

        return $res;
    }

    /**
     * Batched cached backend GETs. $specs is keyed:
     *   ['props' => ['endpoint' => '/admin/properties', 'params' => [...], 'ttl' => 120]]
     * Cache hits (memo + shared) resolve instantly (~1ms); stale entries serve
     * instantly and refresh in background; only true misses fly together
     * over one curl_multi handle with a fail-fast timeout.
     * Error responses are never cached, same as get().
     *
     * @param array<string, array{endpoint:string,params?:array,ttl?:int}> $specs
     * @return array<string, ?array> raw responses keyed like $specs
     */
    public function getMulti(array $specs, array $headers = [], int $timeout = 3): array
    {
        $timeout = min(max(1, $timeout), self::FAST_TIMEOUT);
        $now = time();
        $out = [];
        $misses = [];
        foreach ($specs as $key => $spec) {
            $endpoint = (string)($spec['endpoint'] ?? '');
            $params = is_array($spec['params'] ?? null) ? $spec['params'] : [];
            $ttl = (int)($spec['ttl'] ?? 60);
            $cacheKey = $this->keyFor($endpoint, $params, $headers);
            if (isset(self::$memo[$cacheKey])) {
                $hit = self::$memo[$cacheKey];
                if (($hit['exp'] ?? 0) > $now) {
                    $out[$key] = $hit['data'];
                    continue;
                }
                if (($hit['stale_until'] ?? 0) > $now && !empty($hit['data'])) {
                    $out[$key] = $hit['data'];
                    $this->refreshInBackground($endpoint, $params, $headers, $ttl, $cacheKey);
                    continue;
                }
                unset(self::$memo[$cacheKey]);
            }
            try {
                $cached = Cache::read($cacheKey, 'default');
                if (is_array($cached) && isset($cached['exp'], $cached['data'])) {
                    if ($cached['exp'] > $now) {
                        self::$memo[$cacheKey] = $cached;
                        $out[$key] = $cached['data'];
                        continue;
                    }
                    $staleUntil = (int)($cached['stale_until'] ?? ($cached['exp'] + self::STALE_WINDOW));
                    if ($staleUntil > $now && !empty($cached['data'])) {
                        self::$memo[$cacheKey] = $cached;
                        $out[$key] = $cached['data'];
                        $this->refreshInBackground($endpoint, $params, $headers, $ttl, $cacheKey);
                        continue;
                    }
                }
            } catch (\Throwable $e) {
            }
            $misses[$key] = ['endpoint' => $endpoint, 'params' => $params, 'ttl' => $ttl, 'cacheKey' => $cacheKey];
        }

        if ($misses !== []) {
            $batch = [];
            foreach ($misses as $key => $m) {
                $batch[$key] = ['endpoint' => $m['endpoint'], 'params' => $m['params']];
            }
            $fresh = $this->api->getMulti($batch, $headers, $timeout);
            foreach ($misses as $key => $m) {
                $res = $fresh[$key] ?? null;
                if ($res === null) {
                    $stale = $this->readStale($m['cacheKey']);
                    $out[$key] = $stale ?? $res;
                    continue;
                }
                $out[$key] = $res;
                if (is_array($res) && (empty($res['_status']) || (int)$res['_status'] < 400)) {
                    $this->store($m['cacheKey'], $res, $m['ttl']);
                }
            }
        }

        return $out;
    }

    private function store(string $cacheKey, array $data, int $ttl): void
    {
        $ttl = max(5, $ttl);
        $entry = ['exp' => time() + $ttl, 'stale_until' => time() + $ttl + self::STALE_WINDOW, 'data' => $data];
        self::$memo[$cacheKey] = $entry;
        try {
            Cache::write($cacheKey, $entry, 'default');
            $this->register($cacheKey);
        } catch (\Throwable $e) {
        }
    }

    private function readStale(string $cacheKey): ?array
    {
        if (isset(self::$memo[$cacheKey]) && !empty(self::$memo[$cacheKey]['data'])) {
            return self::$memo[$cacheKey]['data'];
        }
        try {
            $cached = Cache::read($cacheKey, 'default');
            if (is_array($cached) && !empty($cached['data'])) {
                return $cached['data'];
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    /**
     * Refresh one entry after the response flushes so stale serves stay ~1ms.
     * Deduplicated per request; never throws; skipped in CLI/test.
     */
    private function refreshInBackground(string $endpoint, array $params, array $headers, int $ttl, string $cacheKey): void
    {
        static $scheduled = [];
        if (isset($scheduled[$cacheKey])) {
            return;
        }
        $scheduled[$cacheKey] = true;
        if (PHP_SAPI === 'cli') {
            return;
        }
        try {
            register_shutdown_function(function () use ($endpoint, $params, $headers, $ttl, $cacheKey): void {
                try {
                    if (function_exists('fastcgi_finish_request')) {
                        @fastcgi_finish_request();
                    }
                    $fresh = $this->api->get($endpoint, $params, $headers, self::FAST_TIMEOUT);
                    if (is_array($fresh) && (empty($fresh['_status']) || (int)$fresh['_status'] < 400)) {
                        $this->store($cacheKey, $fresh, $ttl);
                    }
                } catch (\Throwable $e) {
                }
            });
        } catch (\Throwable $e) {
        }
    }

    private function keyFor(string $endpoint, array $params, array $headers): string
    {
        return 'portal_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $endpoint) . '_' . md5($endpoint . '|' . (string)json_encode($this->normalize($params)) . '|' . md5((string)json_encode($headers)));
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
