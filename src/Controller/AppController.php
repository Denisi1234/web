<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;

/**
 * Application Controller
 * Global controller providing authentication state, user profile, and flash messaging across all views.
 */
class AppController extends Controller
{
   
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $session = $this->getRequest()->getSession();
        $isLoggedOut = (bool)$session->read('is_logged_out');
        $sessionUser = $session->read('User');

        // Transparent re-login: session file lost (multi-instance host,
        // GC, browser restart) but persistent cookie present → rebuild
        // session from the verified backend token. Explicit logout
        // (is_logged_out) is never overridden.
        if (empty($sessionUser) && !$isLoggedOut) {
            try {
                $authService = new \App\Service\AuthService();
                $cookieToken = $authService->readToken($this->getRequest());
                if ($cookieToken !== '') {
                    $restored = $authService->restoreSession($session, $cookieToken);
                    if ($restored !== null) {
                        $sessionUser = $restored;
                    }
                }
            } catch (\Throwable $e) {
                // never break rendering on auth recovery
            }
        }

        $isLoggedIn = !$isLoggedOut && !empty($sessionUser) && (!empty($sessionUser['id']) || !empty($sessionUser['email']));
        $userProfile = $isLoggedIn ? $sessionUser : null;

        // Sliding persistence: session holds a token but the browser never
        // got the cookie (logged in before the cookie shipped, cookie
        // wiped, new device flow) → mint it now so the NEXT request and
        // every portal activity stays authenticated without re-login.
        // This is what finally stops spurious "Session expired" bounces.
        try {
            $sessionToken = trim((string)$session->read('auth_token'));
            if ($sessionToken !== '' && !$isLoggedOut) {
                $authService = new \App\Service\AuthService();
                $cookies = $this->getRequest()->getCookieParams();
                if (empty($cookies[\App\Service\AuthService::TOKEN_COOKIE])) {
                    $this->setResponse($authService->persistToken($this->response, $sessionToken));
                }
            }
        } catch (\Throwable $e) {
            // never break rendering on cookie housekeeping
        }
        // Collapse identical queued flash messages (repeated auth redirects,
        // background prefetch kicks) so users see each notice once, not ×12.
        try {
            $flashes = $session->read('Flash.flash');
            if (is_array($flashes) && count($flashes) > 1) {
                $seen = [];
                $unique = [];
                foreach ($flashes as $f) {
                    $sig = ($f['key'] ?? 'flash') . '|' . (string)($f['message'] ?? '');
                    if (!isset($seen[$sig])) {
                        $seen[$sig] = true;
                        $unique[] = $f;
                    }
                }
                if (count($unique) !== count($flashes)) {
                    $session->write('Flash.flash', $unique);
                }
            }
        } catch (\Throwable $e) {
            // never break rendering on flash housekeeping
        }
        // Preserve controller-provided profile: AdminOwnerController and
        // HostController use cachedProfile() which reads from the backend
        // (with Redis/file cache + stale fallback). This profile survives
        // session loss because it only needs the persistent cookie token.
        // Without this guard, the $this->set() below would OVERWRITE the
        // valid controller profile with null (when session is empty and
        // restoreSession fails), making the sidebar/topbar show "Host"
        // instead of "Administrator" — the root cause of every action
        // appearing to "log you out".
        $controllerProfile = $this->viewBuilder()->getVar('userProfile');
        if (
            is_array($controllerProfile)
            && !empty($controllerProfile['id'])
            && !empty($controllerProfile['role'])
        ) {
            // Controller already set a backend-verified profile — keep it.
            // Derive isLoggedIn from it; session state may be stale.
            $this->set('isLoggedIn', true);
        } else {
            $this->set(compact('userProfile', 'isLoggedIn'));
        }
    }
}
