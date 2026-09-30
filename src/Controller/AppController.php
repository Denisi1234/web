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

        $isLoggedIn = !$isLoggedOut && !empty($sessionUser) && (!empty($sessionUser['id']) || !empty($sessionUser['email']));
        $userProfile = $isLoggedIn ? $sessionUser : null;

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

        $this->set(compact('userProfile', 'isLoggedIn'));
    }
}
