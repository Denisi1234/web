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

    public function __construct(?string $baseUrl = null, int $timeout = 6)
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
     */
    public function get(string $endpoint, array $queryParams = [], array $headers = []): ?array
    {
        return $this->request('GET', $endpoint, $queryParams, $headers);
    }

    /**
     * Perform POST request to backend API
     */
    public function post(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Perform PUT request to backend API
     */
    public function put(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('PUT', $endpoint, $data, $headers);
    }

    public function delete(string $endpoint, array $headers = []): ?array
    {
        return $this->request('DELETE', $endpoint, [], $headers);
    }

    /**
     * Internal request executor — bubbles 4xx/5xx as JSON with _status, retries timeouts, caches safe GETs
     */
    protected function request(string $method, string $endpoint, array $data = [], array $headers = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $isGet = strtoupper($method) === 'GET';
        $cacheKey = null;
        // Per-route cache for safe GETs (60s) — /properties, /map-config
        if ($isGet && (str_contains($endpoint, '/properties') || str_contains($endpoint, '/map-config'))) {
            $cacheKey = 'fastnet_api_' . md5($method . $endpoint . json_encode($data));
            $cached = \Cake\Cache\Cache::read($cacheKey, 'default');
            if (is_array($cached)) return $cached;
        }
        $options = [
            'headers' => array_merge(['Accept' => 'application/json'], $headers)
        ];

        $attempts = 0;
        $maxAttempts = 2;
        while ($attempts < $maxAttempts) {
            try {
                if (strtoupper($method) === 'POST') {
                    $response = $this->http->post($url, $data, $options);
                } elseif (strtoupper($method) === 'PUT') {
                    $response = $this->http->put($url, $data, $options);
                } elseif (strtoupper($method) === 'DELETE') {
                    $response = $this->http->delete($url, $options);
                } else {
                    $response = $this->http->get($url, $data, $options);
                }

                $status = $response->getStatusCode();
                if ($response->isOk() || $status === 201) {
                    $json = $response->getJson();
                    if ($cacheKey && is_array($json)) \Cake\Cache\Cache::write($cacheKey, $json);
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
}
