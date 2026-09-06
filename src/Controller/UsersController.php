<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Client;
use Cake\Http\Response;

class UsersController extends AppController
{
    /**
     * Index action - redirect to user profile
     */
    public function index(): ?Response
    {
        return $this->redirect(['controller' => 'Pages', 'action' => 'myProfile']);
    }

    /**
     * User profile action - redirect to my-profile
     */
    public function profile(): ?Response
    {
        return $this->redirect(['controller' => 'Pages', 'action' => 'myProfile']);
    }

    /**
     * User login action
     */
    public function login(): ?Response
    {
        return $this->redirect('/login');
    }

    /**
     * User logout action
     */
    public function logout(): ?Response
    {
        $session = $this->getRequest()->getSession();
        $authService = new \App\Service\AuthService();
        $authService->logout($session);

        $this->Flash->success(__('You have been successfully logged out.'));
        return $this->redirect('/');
    }

    /**
     * Register / Signup action
     */
    public function register(): ?Response
    {
        return $this->redirect('/signup');
    }
}
