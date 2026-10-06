<?php
declare(strict_types=1);

namespace App\Service\Portal;

use Cake\Cache\Cache;

/**
 * PortalCacheTrait — Shared-cache store, stale reads, background refresh, and invalidation.
 */
trait PortalCacheTrait
{
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
