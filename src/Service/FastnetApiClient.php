<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Log\Log;
use function Cake\Core\env;

/**
 * FastnetApiClient
 * 
 * Centralized, environment-aware REST API client connecting
 * fastnetstays.com frontend to the fastnet_backed microservices.
 */
class FastnetApiClient
{
    protected Client $http;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(?string $baseUrl = null, int $timeout = 10)
    {
        $this->timeout = $timeout;
        $this->baseUrl = $baseUrl ?: (string)Configure::read(
            'App.backendApiUrl',
            env('BACKEND_API_URL', 'http://127.0.0.1:8000/api')
        );
        $this->baseUrl = rtrim($this->baseUrl, '/');

        $this->http = new Client([
            'timeout' => $this->timeout,
            'headers' => [
                'Accept'     => 'application/json',
                'User-Agent' => 'FastNetStays/1.0 (CakePHP 5; fastnetstays.com)'
            ]
        ]);
    }

    /**
     * Get active backend API URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Perform GET request to backend API
     * $timeout overrides default (search passes 4s for fail-fast).
     */
    public function get(string $endpoint, array $queryParams = [], array $headers = [], ?int $timeout = null): ?array
    {
        return $this->request('GET', $endpoint, $queryParams, $headers, $timeout);
    }

    /**
     * Perform POST request to backend API
     */
    public function post(string $endpoint, array $data = [], array $headers = []): ?array
    {
        $res = $this->request('POST', $endpoint, $data, $headers);
        $this->clearPropertiesCache();
        return $res;
    }

    /**
     * Perform PUT request to backend API
     */
    public function put(string $endpoint, array $data = [], array $headers = []): ?array
    {
        $res = $this->request('PUT', $endpoint, $data, $headers);
        $this->clearPropertiesCache();
        return $res;
    }

    /**
     * Perform concurrent POST requests to prevent PHP worker blocking on loops.
     */
    public function postMulti(array $requests, array $headers = []): array
    {
        $mh = curl_multi_init();
        $chList = [];
        foreach ($requests as $i => $req) {
            $ch = curl_init($this->baseUrl . '/' . ltrim($req['endpoint'], '/'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req['data'] ?? []));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            
            $reqHeaders = array_merge(['Accept: application/json', 'Content-Type: application/json'], $headers);
            $flatHeaders = [];
            foreach ($reqHeaders as $k => $v) {
                $flatHeaders[] = is_int($k) ? $v : "$k: $v";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $flatHeaders);
            
            curl_multi_add_handle($mh, $ch);
            $chList[$i] = $ch;
        }
        
        $active = null;
        do { curl_multi_exec($mh, $active); } while ($active);
        
        $results = [];
        foreach ($chList as $i => $ch) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $json = json_decode((string)curl_multi_getcontent($ch), true);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            if (is_array($json) && $status >= 400) $json['_status'] = $status;
            $results[$i] = $json;
        }
        curl_multi_close($mh);
        
        $this->clearPropertiesCache();
        return $results;
    }

    public function patch(string $endpoint, array $data = [], array $headers = []): ?array
    {
        $res = $this->request('PATCH', $endpoint, $data, $headers);
        $this->clearPropertiesCache();
        return $res;
    }

    public function delete(string $endpoint, array $headers = []): ?array
    {
        return $this->request('DELETE', $endpoint, [], $headers);
    }

    /**
     * Internal request executor — backend is source of truth.
     * Only the static /map-config GET is cached (5 min); all admin/host
     * data (properties, rooms, bookings, finance, users) is always live.
     * 4xx/5xx bubble as JSON with _status so callers never fallback to mocks silently.
     */
    protected function request(string $method, string $endpoint, array $data = [], array $headers = [], ?int $timeout = null): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $isGet = strtoupper($method) === 'GET';
        $cacheKey = null;
        // Only cache static config — never mutable portal data
        if ($isGet && str_contains($endpoint, '/map-config')) {
            $cacheKey = 'fastnet_api_' . md5($method . $endpoint . json_encode($data));
            $cached = \Cake\Cache\Cache::read($cacheKey, 'default');
            if (is_array($cached)) return $cached;
        }
        // Fail-fast client for search: short timeout avoids blocking page on slow backend.
        $http = $this->http;
        if ($timeout !== null && $timeout !== $this->timeout) {
            $http = new Client([
                'timeout' => $timeout,
                'headers' => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'FastNetStays/1.0 (CakePHP 5; fastnetstays.com)'
                ]
            ]);
        }
        $options = [
            'headers' => array_merge(['Accept' => 'application/json'], $headers)
        ];

        $attempts = 0;
        // Retry transient timeouts only for idempotent methods — retrying
        // POST can execute a write twice (double booking/payment/room).
        $maxAttempts = strtoupper($method) === 'POST' ? 1 : 2;
        while ($attempts < $maxAttempts) {
            try {
                if (strtoupper($method) === 'POST') {
                    $response = $http->post($url, $data, $options);
                } elseif (strtoupper($method) === 'PUT') {
                    $response = $http->put($url, $data, $options);
                } elseif (strtoupper($method) === 'PATCH') {
                    $response = $http->patch($url, $data, $options);
                } elseif (strtoupper($method) === 'DELETE') {
                    $response = $http->delete($url, $options);
                } else {
                    $response = $http->get($url, $data, $options);
                }

                $status = $response->getStatusCode();
                if ($response->isOk() || $status === 201) {
                    $json = $response->getJson();
                    // 5-min TTL on static config only; portal data stays live
                    if ($cacheKey && is_array($json)) \Cake\Cache\Cache::write($cacheKey, $json, 'default');
                    return $json;
                }
                // Bubble all 4xx/5xx as JSON with _status so callers don't fallback to mocks silently
                $json = null;
                try { $json = $response->getJson(); } catch (\Throwable $e) { $json = null; }
                if (is_array($json)) {
                    $json['_status'] = $status;
                    return $json;
                }
                return ['message' => trim((string)$response->getBody()) ?: 'Request failed', '_status' => $status];

            } catch (\Throwable $e) {
                $attempts++;
                $isTimeout = str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'cURL');
                if ($isTimeout && $attempts < $maxAttempts) {
                    usleep(100000 * $attempts); // 100ms backoff
                    continue;
                }
                Log::error(sprintf('[FastnetApiClient] %s %s failed after %d attempts: %s', $method, $url, $attempts, $e->getMessage()));
                return null;
            }
        }
        return null;
    }

    /**
     * Only static /map-config is cached, and writes never affect it,
     * so there is nothing to invalidate — backend stays source of truth.
     */
    private function clearPropertiesCache(): void
    {
    }
}
