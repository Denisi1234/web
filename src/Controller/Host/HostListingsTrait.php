<?php
declare(strict_types=1);

namespace App\Controller\Host;

/**
 * HostListingsTrait — Owner-scoped lists, cached profile, dashboard, and listings.
 */
trait HostListingsTrait
{
    /**
     * Owner-scoped property list, backend as source of truth.
     * Admin → /admin/properties (all, has host_id).
     * Owner → /properties?mine=1 (own only, incl. pending/roomless).
     * Falls back to client-side host_id filter when items carry it.
     * Caches in session for 60s to make portal navigations instantaneous.
     */
    private function myProperties(array $headers): array
    {
        if ($this->rawToken() === '') return [];
        $session = $this->getRequest()->getSession();
        $cached = $session->read('HostProperties');
        $ts = (int)$session->read('HostPropertiesTs');
        if (is_array($cached) && !empty($cached) && $ts > time() - 60) {
            return $cached;
        }

        if ($this->isAdminUser()) {
            $this->releaseSession();
            $res = $this->apiClient->get('/admin/properties', [], $headers);
            $this->markBackend($res);
            if ($res === null) return is_array($cached) ? $cached : [];
            if (!empty($res) && empty($res['_status'])) {
                $list = $res['data'] ?? (isset($res[0]) ? $res : []);
                if (is_array($list)) {
                    $result = array_values($list);
                    $session->write('HostProperties', $result);
                    $session->write('HostPropertiesTs', time());
                    return $result;
                }
            }
        } else {
            $this->releaseSession();
            $res = $this->apiClient->get('/properties', ['mine' => 1, 'per_page' => 50], $headers);
            $this->markBackend($res);
            if ($res === null) return is_array($cached) ? $cached : [];
            if (!empty($res) && empty($res['_status'])) {
                $list = $res['data'] ?? (isset($res[0]) ? $res : []);
                if (is_array($list)) {
                    $result = array_values($list);
                    $session->write('HostProperties', $result);
                    $session->write('HostPropertiesTs', time());
                    return $result;
                }
            }
        }
        // Fallback: unscoped list filtered by host_id when present
        $this->releaseSession();
        $res = $this->apiClient->get('/properties', ['per_page' => 50], $headers);
        $this->markBackend($res);
        if ($res === null) return is_array($cached) ? $cached : [];
        $list = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($list)) return is_array($cached) ? $cached : [];
        $oid = $this->ownerId();
        if (!$this->isAdminUser() && $oid > 0) {
            $scoped = array_values(array_filter($list, fn($p) => (int)($p['host_id'] ?? $p['host']['id'] ?? 0) === $oid));
            // Only use filtered result if items actually carry ownership info
            foreach ($list as $p) {
                if (isset($p['host_id']) || isset($p['host']['id'])) {
                    $session->write('HostProperties', $scoped);
                    $session->write('HostPropertiesTs', time());
                    return $scoped;
                }
            }
        }
        $result = array_values($list);
        $session->write('HostProperties', $result);
        $session->write('HostPropertiesTs', time());
        return $result;
    }

    /**
     * Owner-scoped bookings. Owner → /bookings first (server-scoped by host);
     * admin → /admin/bookings first. Caches in session for 60s.
     */
    private function myBookings(array $headers): array
    {
        if ($this->rawToken() === '') return [];
        $session = $this->getRequest()->getSession();
        $cached = $session->read('HostBookings');
        $ts = (int)$session->read('HostBookingsTs');
        if (is_array($cached) && !empty($cached) && $ts > time() - 60) {
            return $cached;
        }

        $first = $this->isAdminUser() ? '/admin/bookings' : '/bookings';
        $second = $this->isAdminUser() ? '/bookings' : '/admin/bookings';
        $this->releaseSession();
        $res = $this->apiClient->get($first, [], $headers);
        if ($res === null) {
            $this->markBackend($res);
            return is_array($cached) ? $cached : [];
        }
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->apiClient->get($second, [], $headers);
        }
        $this->markBackend($res);
        $list = $res['data'] ?? (isset($res[0]) ? $res : []);
        $result = is_array($list) ? array_values($list) : [];
        $session->write('HostBookings', $result);
        $session->write('HostBookingsTs', time());
        return $result;
    }

    /**
     * Session-cached profile (120s TTL) — backend /user/personal-details
     * takes ~2.3s, and sidebar/topbar only need it for display.
     * Backend stays source of truth; refresh on profile update.
     * Releases the session lock before/after slow I/O so parallel
     * portal requests (prefetch) don't serialize on the session file.
     */
    private function cachedProfile(): array
    {
        if ($this->rawToken() === '') return [];
        $session = $this->getRequest()->getSession();
        $cached = $session->read('UserProfile');
        $ts = (int)$session->read('UserProfileTs');
        if (is_array($cached) && !empty($cached) && $ts > time() - 120) {
            $session->close();
            return $cached;
        }
        $session->close();
        $fresh = $this->authService->getPersonalDetails($this->rawToken());
        if (!empty($fresh) && !empty($fresh['id'])) { // id required: public personal-details returns a demo profile (null id) for bad tokens
            $session->write('UserProfile', $fresh);
            $session->write('UserProfileTs', time());
            $session->close();
            return $fresh;
        }
        return is_array($cached) && !empty($cached) ? $cached : $fresh;
    }

    public function dashboard()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $properties = $this->myProperties($headers);

        $bookings = $this->myBookings($headers);

        $stats = [
            'properties' => count($properties),
            'bookings' => count($bookings),
            'revenue' => array_sum(array_map(fn($b) => (float)($b['total_price'] ?? 0), $bookings)),
            // Per-booking stored values, so a non-standard commission rate
            // reconciles with the payout ledger instead of showing zero.
            'platform_fee' => array_sum(array_map(fn($b) => (float)($b['platform_fee'] ?? 0), $bookings)),
            'owner_earnings' => array_sum(array_map(fn($b) => (float)($b['owner_payout'] ?? $b['owner_earnings'] ?? 0), $bookings)),
        ];
        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'properties', 'bookings', 'stats', 'backendError'));
        return $this->render('/Pages/host-dashboard');
    }

    public function listings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $properties = $this->myProperties($headers);
        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'properties', 'backendError'));
        return $this->render('/Pages/host-listings');
    }

    /**
     * Cache-bust bridge for direct-API saves: the browser writes straight to
     * the backend, then hits this (fast, no backend call) to drop stale
     * session caches (profile, onboarding draft) before landing on the list
     * page. `go` is restricted to local paths. quiet=1 returns 204 for
     * background busts after optimistic saves.
     */
    public function cacheBust()
    {
        $q = $this->getRequest()->getQueryParams();
        $scope = array_values(array_filter(array_map('trim', explode(',', (string)($q['scope'] ?? '')))));
        if (in_array('profile', $scope, true)) {
            $sess = $this->getRequest()->getSession();
            $sess->delete('UserProfile');
            $sess->delete('UserProfileTs');
        }
        if (!empty($q['draft'])) {
            $this->getRequest()->getSession()->delete('OnboardDraft');
        }
        if (!empty($q['quiet'])) {
            $this->autoRender = false;
            return $this->response->withStatus(204);
        }
        $go = (string)($q['go'] ?? '/host/dashboard');
        if (!str_starts_with($go, '/') || str_starts_with($go, '//')) {
            $go = '/host/dashboard';
        }
        return $this->redirect($go);
    }
}
