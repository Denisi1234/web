<?php
declare(strict_types=1);

namespace App\Controller\Pages;

use Cake\Http\Response;

/**
 * PagesSupportTrait — Help center, FAQ, contact, and the API proxy.
 */
trait PagesSupportTrait
{
    public function helpCenter()
    {
        // Real help-centre feed: popular topics (with real action URLs),
        // support contact from backend config, and the signed-in guest's
        // upcoming stay. Public endpoint — works logged out too. Never
        // fabricated: on backend failure the page renders contact + FAQs.
        $helpCentre = null;
        try {
            $token = $this->currentBearerToken();
            $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];
            $res = $this->apiClient->get('/support/help-centre', [], $headers, 6);
            if (is_array($res) && ($res['status'] ?? '') === 'success' && empty($res['_status'])) {
                $helpCentre = $res;
            }
        } catch (\Throwable $e) {
            $helpCentre = null;
        }
        $session = $this->getRequest()->getSession();
        $isLoggedIn = $this->authService->isAuthenticated($session);
        $this->set(compact('helpCentre', 'isLoggedIn'));
        return $this->render('/Pages/help-center');
    }
    public function faq()
    {
        // Support email only — office addresses and phone numbers on the old
        // page were unverified, so they are not rendered anymore.
        $supportEmail = '';
        try {
            $res = $this->apiClient->get('/support/help-centre', [], [], 6);
            if (is_array($res) && ($res['status'] ?? '') === 'success') {
                $supportEmail = trim((string)($res['support_contact']['email'] ?? ''));
            }
        } catch (\Throwable $e) {
            $supportEmail = '';
        }
        $this->set(compact('supportEmail'));
        return $this->render('/Pages/faq');
    }

    public function contactV1()
    {
        // Real support contact from backend config (email only — no invented
        // phone numbers). Ticket submission needs auth server-side, so guests
        // get the direct email path instead of a form that can never send.
        $supportEmail = '';
        try {
            $res = $this->apiClient->get('/support/help-centre', [], [], 6);
            if (is_array($res) && ($res['status'] ?? '') === 'success') {
                $supportEmail = trim((string)($res['support_contact']['email'] ?? ''));
            }
        } catch (\Throwable $e) {
            $supportEmail = '';
        }
        $session = $this->getRequest()->getSession();
        $isLoggedIn = $this->authService->isAuthenticated($session);
        $this->set(compact('supportEmail', 'isLoggedIn'));
        return $this->render('/Pages/contact-v1');
    }

    /**
     * Strict allowlist match for the API proxy: the exact path or a real
     * sub-path only. "/properties" matches "/properties/5" but never
     * "/properties-evil" (the old loose prefix check allowed those).
     */
    private function proxyPathMatches(string $apiPath, string $prefix): bool
    {
        if ($apiPath === $prefix) return true;
        return str_starts_with($apiPath, $prefix . '/');
    }

    // ── Universal API Proxy (localhost & production) ─────────────────────

    public function apiProxy(string ...$path): Response
    {
        $apiPath = '/' . implode('/', $path);
        $method = strtolower($this->getRequest()->getMethod());

        // Reject traversal / encoding tricks outright — the proxy must only
        // ever forward clean sub-paths of the allowlisted resources.
        $lowerPath = strtolower($apiPath);
        if (
            $apiPath === '' || $apiPath === '/' ||
            str_contains($apiPath, '..') || str_contains($apiPath, '\\') ||
            str_contains($apiPath, "\0") || str_contains($lowerPath, '%2e') ||
            str_contains($lowerPath, '%00') || str_contains($lowerPath, '%5c')
        ) {
            return $this->response->withStatus(403)->withType('application/json')->withStringBody(json_encode(['error' => 'Proxy path not allowed']));
        }

        $allowedGetPrefixes = [
            '/map-config', '/properties', '/rooms', '/destinations',
            '/auth/verify', '/user/personal-details', '/bookings/calculate',
            '/alerts', '/search/suggestions', '/currencies',
            '/notifications/preferences', '/travel/preferences', '/support/help-centre'
        ];
        $allowedPostPrefixes = [
            '/notifications/preferences', '/travel/preferences',
            '/receipts/generate', '/feedback/accessibility',
            '/user/personal-details', '/alerts',
            // AzamPay transaction callback lands here in production
            // (callback URL is https://fastnetstays.com/api/payments/webhook).
            // The backend owns validation (no success default, amount match
            // enforced), so forwarding the payload is safe.
            '/payments/webhook',
        ];

        // Exactly one property POST: the AI description draft. Scoped to a
        // suffix rather than opening all of POST /properties, and the backend
        // enforces that the caller owns the property.
        $isGenerateDescription = (bool) preg_match(
            '#^/properties/\d+/generate-description$#',
            $apiPath
        );
        $allowedDeletePrefixes = ['/alerts', '/wishlist'];

        $isAllowed = false;
        if ($method === 'get') {
            foreach ($allowedGetPrefixes as $p) {
                if ($this->proxyPathMatches($apiPath, $p)) {
                    $isAllowed = true; break;
                }
            }
        } elseif ($method === 'post') {
            if ($isGenerateDescription) {
                $isAllowed = true;
            }
            foreach ($allowedPostPrefixes as $p) {
                if ($this->proxyPathMatches($apiPath, $p)) {
                    $isAllowed = true; break;
                }
            }
        } elseif ($method === 'delete') {
            foreach ($allowedDeletePrefixes as $p) {
                if ($this->proxyPathMatches($apiPath, $p)) {
                    $isAllowed = true; break;
                }
            }
        }

        if (!$isAllowed) {
            return $this->response->withStatus(403)->withType('application/json')->withStringBody(json_encode(['error' => 'Proxy path not allowed']));
        }

        // Simple per-IP rate limit: 120/min
        $ip = $this->getRequest()->clientIp() ?? 'unknown';
        $cacheKey = 'api_proxy_rate_' . md5($ip . $apiPath);
        $rate = \Cake\Cache\Cache::read($cacheKey, 'default');
        $rateCount = (is_array($rate) && isset($rate['exp']) && $rate['exp'] > time()) ? (int)$rate['count'] : 0;
        if ($rateCount >= 120) {
            return $this->response->withStatus(429)->withType('application/json')->withStringBody(json_encode(['error' => 'Rate limit exceeded']));
        }
        \Cake\Cache\Cache::write($cacheKey, ['count' => $rateCount + 1, 'exp' => time() + 60]);

        $token = $this->authService->readToken($this->getRequest());
        $headers = $token !== '' ? ['Authorization' => 'Bearer ' . $token] : [];

        $queryParams = $this->getRequest()->getQueryParams();
        $body = $this->getRequest()->getData();

        if ($method === 'post') {
            $data = $this->apiClient->post($apiPath, (array)$body, $headers);
        } elseif ($method === 'delete') {
            $delPath = $apiPath . ($queryParams ? '?' . http_build_query($queryParams) : '');
            $data = $this->apiClient->delete($delPath, $headers);
        } else {
            $data = $this->apiClient->get($apiPath, $queryParams, $headers);
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode($data ?? []));
    }
}
