<?php
declare(strict_types=1);

namespace App\Controller\Host;

/**
 * HostReviewsTrait — Guest reviews on the host's own properties.
 * Same Carbon table pattern as the admin reviews page. The backend scopes
 * GET /admin/reviews to the caller's lodges for non-admins, so a host only
 * ever sees their own reviews here. Read-only: reply/moderate endpoints
 * don't exist on the backend.
 */
trait HostReviewsTrait
{
    public function reviews()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $this->releaseSession();
        $res = $this->apiClient->get('/admin/reviews', ['per_page' => 50], $headers);
        $this->markBackend($res);
        if ($bounce = $this->bounceOnUnauth($res, '/host/reviews')) {
            return $bounce;
        }
        $reviews = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($reviews)) {
            $reviews = [];
        }
        if (empty($reviews) && !empty($res) && empty($res['_status']) && isset($res['id'])) {
            $reviews = [$res];
        }

        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'reviews', 'backendError'));

        return $this->render('/Pages/host-reviews');
    }
}
