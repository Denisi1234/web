<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * AdminOverviewTrait — Dashboard, owners, and lodges reads.
 */
trait AdminOverviewTrait
{

    public function dashboard()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $this->releaseSession();

        // One parallel batch instead of 4 sequential HTTP calls per cold view.
        // Slow-moving aggregates get long TTLs + stale-while-revalidate, so
        // warm views serve in ~1ms with zero backend I/O; bookings stay short.
        $batch = $this->portal->getMulti([
            'props' => ['endpoint' => '/admin/properties', 'params' => [], 'ttl' => 300],
            'books' => ['endpoint' => '/admin/bookings', 'params' => [], 'ttl' => 60],
            'users' => ['endpoint' => '/admin/users', 'params' => ['role' => 'owner'], 'ttl' => 300],
            'verif' => ['endpoint' => '/admin/verification/summary', 'params' => [], 'ttl' => 600],
        ], $headers);
        $propRes = $batch['props'];
        if ($bounce = $this->bounceOnUnauth($propRes, '/admin/dashboard')) return $bounce;
        if (empty($propRes) || !empty($propRes['_status'])) {
            $propRes = $this->portal->get('/properties', [], $headers, 120);
        }
        $properties = $propRes['data'] ?? (isset($propRes[0]) ? $propRes : []);
        if (!is_array($properties)) $properties = [];

        $bookRes = $batch['books'];
        $bookings = $bookRes['data'] ?? (isset($bookRes[0]) ? $bookRes : []);
        if (!is_array($bookings)) $bookings = [];

        $userRes = $batch['users'];
        $owners = $userRes['data'] ?? (isset($userRes[0]) ? $userRes : []);
        if (!is_array($owners)) $owners = [];

        // Verification queue counts come from one purpose-built aggregate rather
        // than being derived client-side from the full property/booking/user
        // collections above.
        $verRes = $batch['verif'];
        $verification = $verRes['data'] ?? $verRes;
        if (!is_array($verification)) $verification = [];
        $verificationCounts = is_array($verification['counts'] ?? null) ? $verification['counts'] : [];

        // Commission comes from each booking's stored commission_rate /
        // platform_fee / owner_payout. These were previously re-derived as a flat
        // revenue * 0.10 / * 0.90 here, which discarded the real per-booking
        // split and produced figures that could never reconcile with the
        // backend's ledger.
        $revenue = array_sum(array_map(fn($b) => (float)($b['total_price'] ?? 0), $bookings));

        $sumFee = static function (array $rows, string $column): float {
            $total = 0.0;
            foreach ($rows as $row) {
                if (isset($row[$column]) && $row[$column] !== null) {
                    $total += (float) $row[$column];
                }
            }
            return $total;
        };

        $stats = [
            'properties' => count($properties),
            'bookings' => count($bookings),
            'owners' => count($owners),
            'revenue' => $revenue,
            'platform_fee' => $sumFee($bookings, 'platform_fee'),
            'owner_earnings' => $sumFee($bookings, 'owner_payout'),
        ];

        // Recent properties for table
        $recentProperties = array_slice($properties, 0, 8);
        $this->markDown($propRes, $bookRes, $userRes, $verRes);
        $this->set(compact(
            'userProfile', 'properties', 'recentProperties', 'bookings', 'owners',
            'stats', 'verification', 'verificationCounts'
        ));
    }

    public function owners()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $this->releaseSession();

        $q = $this->getRequest()->getQueryParams();
        $page = max(1, (int)($q['page'] ?? 1));
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        $params = ['role' => 'owner', 'page' => $page, 'per_page' => 15];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        // Users + financial summary fly together (was 2 sequential calls).
        // Long TTL + stale-while-revalidate: filter pages serve in ~1ms warm.
        $batch = $this->portal->getMulti([
            'users' => ['endpoint' => '/admin/users', 'params' => $params, 'ttl' => 300],
            'fin' => ['endpoint' => '/admin/owners/financial-summary', 'params' => $params, 'ttl' => 300],
        ], $headers);
        $res = $batch['users'];
        if ($bounce = $this->bounceOnUnauth($res, '/admin/owners')) return $bounce;
        $users = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($users)) $users = [];

        $finRes = $batch['fin'];
        $financial = $finRes['data'] ?? $finRes;
        if (!is_array($financial)) $financial = [];

        $this->markDown($res, $finRes);
        $this->set(compact('userProfile', 'users', 'financial', 'search', 'status', 'page'));
    }

    public function lodges()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $this->releaseSession();

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        // NOTE: kept unpaginated (per_page 50) on purpose — the template has
        // no pager yet and the backend shape is unverified, so capping the
        // fetch could hide listings. Pagination UI is a follow-up, not this.
        $params = ['per_page' => 50];
        if ($search !== '') $params['search'] = $search;
        if ($status !== '') $params['status'] = $status;

        $res = $this->portal->get('/admin/properties', $params, $headers, 300);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/lodges')) return $bounce;
        $properties = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($properties)) $properties = [];
        if (empty($properties) && !empty($res) && empty($res['_status'])) $properties = $res;
        if (empty($properties) && $res === null) {
            // Backend dead: stale already served inside PortalService when
            // available; otherwise render the shell instantly (no hang).
            $properties = [];
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'properties', 'search', 'status'));
    }
}
