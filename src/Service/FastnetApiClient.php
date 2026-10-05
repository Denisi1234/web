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
     * Upload a single file as multipart/form-data.
     *
     * The JSON-only request path cannot carry binary uploads, so this builds
     * the multipart body and calls the backend's POST /upload directly.
     *
     * @param string $field      form field name (the backend expects 'file')
     * @param string $path       readable local path to the file
     * @param string $filename   original client filename
     * @param string $mimeType   content type for the part
     */
    public function uploadFile(string $endpoint, string $field, string $path, string $filename, string $mimeType, array $headers = [], ?int $timeout = 30): ?array
    {
        if (!is_readable($path)) {
            return ['_status' => 400, 'message' => 'Upload file could not be read.'];
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return ['_status' => 400, 'message' => 'Upload file could not be read.'];
        }

        $boundary = '----fastnet' . bin2hex(random_bytes(12));
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'upload';
        $safeMime = preg_match('#^[A-Za-z0-9.+-]+/[A-Za-z0-9.+-]+$#', $mimeType) ? $mimeType : 'application/octet-stream';

        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"{$field}\"; filename=\"{$safeName}\"\r\n"
            . "Content-Type: {$safeMime}\r\n\r\n"
            . $contents . "\r\n"
            . "--{$boundary}--\r\n";
        unset($contents);

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        // Picture uploads on slow production links need longer than the 10s
        // default API timeout — 30s unless the caller overrides.
        $http = $this->http;
        $effTimeout = $timeout ?? 30;
        if ($effTimeout !== $this->timeout) {
            $http = new Client([
                'timeout' => $effTimeout,
                'headers' => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'FastNetStays/1.0 (CakePHP 5; fastnetstays.com)'
                ]
            ]);
        }

        try {
            $response = $http->post($url, $body, [
                'headers' => array_merge([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
                ], $headers),
            ]);

            $status = $response->getStatusCode();
            $json = null;
            try { $json = $response->getJson(); } catch (\Throwable $e) { $json = null; }

            if (($response->isOk() || $status === 201) && is_array($json)) {
                return $json;
            }

            if (is_array($json)) {
                $json['_status'] = $status;
                if ($status >= 400) {
                    Log::error(sprintf('[FastnetApiClient] upload %s -> %d: %s', $url, $status, (string)($json['message'] ?? 'no message')));
                }
                return $json;
            }

            Log::error(sprintf('[FastnetApiClient] upload %s -> %d (non-JSON)', $url, $status));
            return ['_status' => $status, 'message' => 'Upload failed.'];
        } catch (\Throwable $e) {
            Log::error(sprintf('[FastnetApiClient] upload %s failed: %s', $url, $e->getMessage()));
            return ['_status' => 502, 'message' => 'Upload service unavailable. Check BACKEND_API_URL and try again.'];
        }
    }

    /**
     * Perform concurrent POST requests to prevent PHP worker blocking on loops.
     */
    public function postMulti(array $requests, array $headers = [], int $timeout = 15): array
    {
        $mh = curl_multi_init();
        $chList = [];
        $urlList = [];
        foreach ($requests as $i => $req) {
            $url = $this->baseUrl . '/' . ltrim($req['endpoint'], '/');
            $urlList[$i] = $url;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req['data'] ?? []));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

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
        do {
            $mrc = curl_multi_exec($mh, $active);
            if ($active) curl_multi_select($mh, 1);
        } while ($active && $mrc === CURLM_OK);

        $results = [];
        foreach ($chList as $i => $ch) {
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            $raw = (string)curl_multi_getcontent($ch);
            $json = json_decode($raw, true);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            if ($curlErr !== '') {
                Log::error(sprintf('[FastnetApiClient] postMulti %s transport error: %s', $urlList[$i] ?? '?', $curlErr));
                $results[$i] = ['_status' => 502, 'message' => 'Room service unavailable.'];
                continue;
            }
            if (!is_array($json)) {
                Log::error(sprintf('[FastnetApiClient] postMulti %s -> %d (non-JSON)', $urlList[$i] ?? '?', $status));
                $results[$i] = ['_status' => $status ?: 502, 'message' => 'Room service returned an invalid response.'];
                continue;
            }
            if ($status >= 400) {
                $json['_status'] = $status;
                Log::error(sprintf('[FastnetApiClient] postMulti %s -> %d: %s', $urlList[$i] ?? '?', $status, (string)($json['message'] ?? substr($raw, 0, 200))));
            }
            $results[$i] = $json;
        }
        curl_multi_close($mh);

        $this->clearPropertiesCache();
        return $results;
    }

    /**
     * Concurrent GET requests over one curl_multi handle.
     *
     * Portal pages used to fetch 2-4 backend resources sequentially
     * (dashboard = properties + bookings + users + verification), so every
     * cold view paid the sum of all latencies. Batched reads pay roughly the
     * max instead. Response shape matches request(): decoded JSON on success,
     * JSON with _status on 4xx/5xx, transport failures as 502.
     *
     * Fail-fast: connect timeout 2s, total timeout capped at 3s for portal
     * reads so a slow backend degrades to stale/empty in ~1ms server time
     * instead of hanging every portal page.
     *
     * @param array<int|string, array{endpoint:string,params?:array}> $requests
     * @return array<int|string, ?array> keyed like $requests
     */
    public function getMulti(array $requests, array $headers = [], int $timeout = 3): array
    {
        $timeout = min(max(1, $timeout), 5);
        $mh = curl_multi_init();
        $chList = [];
        $urlList = [];
        foreach ($requests as $key => $req) {
            $endpoint = (string)($req['endpoint'] ?? '');
            $params = is_array($req['params'] ?? null) ? $req['params'] : [];
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
            if ($params !== []) {
                $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
            }
            $urlList[$key] = $url;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

            $flatHeaders = ['Accept: application/json'];
            foreach ($headers as $k => $v) {
                $flatHeaders[] = is_int($k) ? (string)$v : "$k: $v";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $flatHeaders);

            curl_multi_add_handle($mh, $ch);
            $chList[$key] = $ch;
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
            if ($active) curl_multi_select($mh, 1);
        } while ($active && $mrc === CURLM_OK);

        $results = [];
        foreach ($chList as $key => $ch) {
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            $raw = (string)curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            if ($curlErr !== '') {
                Log::error(sprintf('[FastnetApiClient] getMulti %s transport error: %s', $urlList[$key] ?? '?', $curlErr));
                $results[$key] = ['_status' => 502, 'message' => 'Service unavailable.'];
                continue;
            }
            $json = json_decode($raw, true);
            if (!is_array($json)) {
                Log::error(sprintf('[FastnetApiClient] getMulti %s -> %d (non-JSON)', $urlList[$key] ?? '?', $status));
                $results[$key] = ['_status' => $status ?: 502, 'message' => 'Service returned an invalid response.'];
                continue;
            }
            if ($status >= 400) {
                $json['_status'] = $status;
            }
            $results[$key] = $json;
        }
        curl_multi_close($mh);

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
