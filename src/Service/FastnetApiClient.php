<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use App\Service\Api\FastnetTransfersTrait;
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

    use FastnetTransfersTrait;

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
        return $this->request('POST', $endpoint, $data, $headers);
    }

    /**
     * Perform PUT request to backend API
     */
    public function put(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('PUT', $endpoint, $data, $headers);
    }

    public function patch(string $endpoint, array $data = [], array $headers = []): ?array
    {
        return $this->request('PATCH', $endpoint, $data, $headers);
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
        // Cache static/public GET endpoints (5-10 min)
        if ($isGet && (
            str_contains($endpoint, '/map-config') || 
            str_contains($endpoint, '/destinations') ||
            (str_contains($endpoint, '/support/help-centre') && empty($headers['Authorization']))
        )) {
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
        // Fail-fast portal reads (timeout <= 3s) never retry: a retry doubles
        // the worst case (3s -> 6s) and PortalService already serves stale
        // instantly, so one attempt is the ~1ms contract.
        $upper = strtoupper($method);
        $maxAttempts = $upper === 'POST' ? 1 : (($timeout !== null && $timeout <= 3) ? 1 : 2);
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
