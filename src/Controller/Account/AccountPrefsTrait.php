<?php
declare(strict_types=1);

namespace App\Controller\Account;

use Cake\Http\Response;

/**
 * AccountPrefsTrait — Recently viewed, preferences, notifications, and locale.
 */
trait AccountPrefsTrait
{
    public function recentlyViewed()
    {
        $session = $this->getRequest()->getSession();
        $token = $this->portalToken();
        $userProfile = $this->authService->getPersonalDetails($token);
        
        $recentStays = $session->read('recently_viewed_stays') ?? [];
        if (!is_array($recentStays)) $recentStays = [];

        // No substitution here. This used to fill an empty "recently viewed" list with
        // three featured properties, so the page showed stays the visitor had
        // never looked at, each with an invented price (120,000), rating (4.8)
        // and review count (150). An empty history is a real state - render it
        // honestly and let the template offer an empty state.
        $this->set(compact('userProfile', 'recentStays'));
        return $this->render('/Pages/recently-viewed');
    }

    public function recentlyViewedClear(): \Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $session->delete('recently_viewed_stays');
        return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 'success', 'message' => 'Cleared']));
    }

    public function recentlyViewedRemove(string $id): \Cake\Http\Response
    {
        $session = $this->getRequest()->getSession();
        $recent = $session->read('recently_viewed_stays') ?? [];
        if (is_array($recent)) {
            $recent = array_values(array_filter($recent, fn($s) => (int)($s['id'] ?? 0) !== (int)$id));
            $session->write('recently_viewed_stays', $recent);
        }
        return $this->response->withType('application/json')->withStringBody(json_encode(['status' => 'success']));
    }

    public function searchPreferences()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $travelPrefs = $this->preferencePayload('travel/preferences');

        $this->set(compact('userProfile', 'travelPrefs'));
        return $this->render('/Pages/search-preferences');
    }

    public function notifications()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $prefs = $this->preferencePayload('notifications/preferences');

        $this->set(compact('userProfile', 'prefs'));
        return $this->render('/Pages/notifications');
    }

    /**
     * GET/POST proxy for a preference endpoint.
     *
     * The pages used to flash "settings saved" without contacting the backend
     * at all, so preferences lived only in localStorage and were lost on any
     * other device. This forwards to the real endpoint with the caller's token.
     */
    private function preferencePayload(string $endpoint): array
    {
        $token = $this->portalToken();
        if ($token === '') {
            return [];
        }
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        if ($this->getRequest()->is(['post', 'put'])) {
            $data = (array)$this->getRequest()->getData();
            $res = $this->apiClient->post('/' . $endpoint, $data, $headers);
            if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                $this->Flash->error(__($res['message'] ?? 'Could not save your preferences.'));
            } elseif (!empty($res)) {
                $this->Flash->success(__('Preferences saved.'));
            }
            return is_array($res['preferences'] ?? null) ? $res['preferences'] : [];
        }

        $res = $this->apiClient->get('/' . $endpoint, [], $headers);

        return is_array($res['preferences'] ?? null) ? $res['preferences'] : [];
    }

    /** GET/POST proxy for /notifications/preferences (called by notifications.php) */
    public function notificationPreferences(): Response
    {
        $prefs = $this->preferencePayload('notifications/preferences');

        return $this->response->withType('application/json')->withStringBody(
            (string)json_encode(['status' => 'success', 'preferences' => $prefs])
        );
    }

    /** GET/POST /travel/preferences — search-preferences.php */
    public function travelPreferences(): Response
    {
        $prefs = $this->preferencePayload('travel/preferences');

        if ($this->getRequest()->is('json') || $this->getRequest()->is('ajax') || $this->getRequest()->accepts('application/json')) {
            return $this->response->withType('application/json')->withStringBody(
                (string)json_encode(['status' => 'success', 'preferences' => $prefs])
            );
        }

        return $this->redirect(['action' => 'searchPreferences']);
    }

    public function languageAndCurrency()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $currRes = $this->apiClient->get('/currencies');
        $currencies = is_array($currRes) && !empty($currRes['currencies']) ? $currRes['currencies'] : [];
        $this->set(compact('userProfile', 'currencies'));
        return $this->render('/Pages/language-and-currency');
    }

    public function settings()
    {
        return $this->redirect(['action' => 'myProfile']);
    }

    public function deleteAccount()
    {
        return $this->redirect(['action' => 'accountSecurity']);
    }
}
