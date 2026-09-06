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

        $this->set(compact('userProfile', 'isLoggedIn'));
    }
}
