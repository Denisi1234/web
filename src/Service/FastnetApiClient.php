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
                'Accept' => 'application/json',
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
     * Internal request executor with error handling & graceful fallback
     */
    protected function request(string $method, string $endpoint, array $data = [], array $headers = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $options = [
            'headers' => array_merge(['Accept' => 'application/json'], $headers)
        ];

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

            if ($response->isOk() || $response->getStatusCode() === 201) {
                return $response->getJson();
            }

            Log::warning(sprintf(
                '[FastnetApiClient] HTTP %s returned status %d: %s',
                $url,
                $response->getStatusCode(),
                substr((string)$response->getBody(), 0, 200)
            ));
        } catch (\Throwable $e) {
            Log::error(sprintf(
                '[FastnetApiClient] Connection failure to %s: %s',
                $url,
                $e->getMessage()
            ));
        }

        return null;
    }
}
