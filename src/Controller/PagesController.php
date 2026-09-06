<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\StaysService;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;

/**
 * PagesController
 * Modular, clean controller for home, static presentation pages, and auth wrappers.
 */
class PagesController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected AuthService $authService;
    protected StaysService $staysService;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
        $this->staysService = new StaysService($this->apiClient);
    }

    public function beforeRender(\Cake\Event\EventInterface $event): void
    {
        parent::beforeRender($event);
        $session = $this->getRequest()->getSession();
        $isLoggedIn = $this->authService->isAuthenticated($session);
        $userProfile = null;
        if ($isLoggedIn) {
            $sessionUser = $session->read('User');
            $userProfile = !empty($sessionUser) ? $sessionUser : $this->authService->getPersonalDetails();
        }
        $this->set(compact('userProfile', 'isLoggedIn'));
    }

    /**
     * Displays a view
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }

    /**
     * Homepage - Real backend properties and exact 4 requested regions
     */
    public function index()
    {
        $featuredResorts = $this->staysService->getFeaturedResorts(50);
        $destinationsSummary = $this->staysService->getDestinationsSummary($featuredResorts);

        $this->set(compact('featuredResorts', 'destinationsSummary'));
        return $this->render('/Pages/index');
    }

    // ── Stays Forwarders (Backward Compatibility) ──────────────────────────
    public function hotelList01()
    {
        return $this->redirect(['controller' => 'Stays', 'action' => 'index', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function hotelDetail($id = null)
    {
        return $this->redirect(['controller' => 'Stays', 'action' => 'detail', $id, '?' => $this->getRequest()->getQueryParams()]);
    }

    public function destination01()
    {
        return $this->redirect(['controller' => 'Stays', 'action' => 'destination01', '?' => $this->getRequest()->getQueryParams()]);
    }

    // ── Host Onboarding ───────────────────────────────────────────────────
    public function joinUs() { return $this->render('/Pages/join-us'); }
    public function addListing() { return $this->render('/Pages/add-listing'); }
    public function addListingStep02() { return $this->render('/Pages/add-listing-step-02'); }
    public function addListingStep03() { return $this->render('/Pages/add-listing-step-03'); }

    // ── Bookings Forwarders (Backward Compatibility) ───────────────────────
    public function bookingPage()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingPage', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function bookingpage02()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingpage02', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function bookingpage03()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingpage03', '?' => $this->getRequest()->getQueryParams()]);
    }

    public function bookingpageSuccess()
    {
        return $this->redirect(['controller' => 'Bookings', 'action' => 'bookingpageSuccess', '?' => $this->getRequest()->getQueryParams()]);
    }

    // ── Account Forwarders (Backward Compatibility) ────────────────────────
    public function menu() { return $this->redirect(['controller' => 'Account', 'action' => 'menu']); }
    public function myProfile() { return $this->redirect(['controller' => 'Account', 'action' => 'myProfile']); }
    public function accountSecurity() { return $this->redirect(['controller' => 'Account', 'action' => 'accountSecurity']); }
    public function myBooking() { return $this->redirect(['controller' => 'Account', 'action' => 'myBooking']); }
    public function paymentDetail() { return $this->redirect(['controller' => 'Account', 'action' => 'paymentDetail']); }
    public function myWishlists() { return $this->redirect(['controller' => 'Account', 'action' => 'myWishlists']); }
    public function recentlyViewed() { return $this->redirect(['controller' => 'Account', 'action' => 'recentlyViewed']); }
    public function searchPreferences() { return $this->redirect(['controller' => 'Account', 'action' => 'searchPreferences']); }
    public function notifications() { return $this->redirect(['controller' => 'Account', 'action' => 'notifications']); }
    public function languageAndCurrency() { return $this->redirect(['controller' => 'Account', 'action' => 'languageAndCurrency']); }
    public function settings() { return $this->redirect(['controller' => 'Account', 'action' => 'settings']); }
    public function deleteAccount() { return $this->redirect(['controller' => 'Account', 'action' => 'deleteAccount']); }

    // ── Authentication Flow ───────────────────────────────────────────────
    public function login()
    {
        $session = $this->getRequest()->getSession();

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();

            // 1. Handle AJAX Session Synchronization (from login.php fetch)
            if (!empty($data['action']) && $data['action'] === 'login_sync' && !empty($data['user'])) {
                $user = (array)$data['user'];
                $token = (string)($data['token'] ?? ($data['access_token'] ?? ''));
                if (!empty($token)) {
                    $user['token'] = $token;
                }
                if (empty($user['first_name']) && !empty($user['name'])) {
                    $user['first_name'] = explode(' ', trim($user['name']))[0];
                }
                $this->authService->syncSession($session, $user);

                return $this->response->withType('application/json')->withStringBody((string)json_encode([
                    'success' => true,
                    'user' => $user
                ]));
            }

            // 2. Handle Traditional Form Login
            $email = (string)($data['email'] ?? '');
            $password = (string)($data['password'] ?? '');

            if (!empty($email) && !empty($password)) {
                $res = $this->authService->login($email, $password);
                $authToken = $res['access_token'] ?? ($res['token'] ?? null);
                $userData = $res['user'] ?? null;

                if (!empty($userData) || !empty($authToken)) {
                    $user = is_array($userData) ? $userData : [];
                    if ($authToken) {
                        $user['token'] = $authToken;
                    }
                    if (empty($user['first_name']) && !empty($user['name'])) {
                        $user['first_name'] = explode(' ', trim($user['name']))[0];
                    }
                    $this->authService->syncSession($session, $user);
                    $this->Flash->success(__('Login successful. Welcome back!'));
                    return $this->redirect('/');
                }
                $this->Flash->error(__('Invalid email or password.'));
            }
        }

        return $this->render('/Pages/login');
    }

    public function signup()
    {
        if ($this->getRequest()->is('post')) {
            $data = $this->getRequest()->getData();
            $this->Flash->success(__('Account created successfully! Welcome to fastnetstays.com.'));
            return $this->redirect('/login');
        }
        return $this->render('/Pages/signup');
    }

    public function forgotPassword() { return $this->render('/Pages/forgot-password'); }
    public function twoFactorAuth() { return $this->render('/Pages/two-factor-auth'); }
    public function resetPassword() { return $this->render('/Pages/reset-password'); }

    // ── Static & Support Pages ────────────────────────────────────────────
    public function aboutUs() { return $this->render('/Pages/about-us'); }
    public function howWeWork() { return $this->render('/Pages/how-we-work'); }
    public function helpCenter() { return $this->render('/Pages/help-center'); }
    public function faq() { return $this->render('/Pages/faq'); }
    public function notFound() { return $this->render('/Pages/404'); }
    public function privacyPolicy() { return $this->render('/Pages/privacy-policy'); }
    public function termsOfService() { return $this->render('/Pages/terms-of-service'); }
    public function contactV1() { return $this->render('/Pages/contact-v1'); }

    // ── Universal API Proxy (localhost & production) ─────────────────────
    public function apiProxy(string ...$path): Response
    {
        $apiPath = '/' . implode('/', $path);
        $method = strtolower($this->getRequest()->getMethod());
        $queryParams = $this->getRequest()->getQueryParams();
        $body = $this->getRequest()->getData();

        if ($method === 'post') {
            $data = $this->apiClient->post($apiPath, (array)$body);
        } else {
            $data = $this->apiClient->get($apiPath, $queryParams);
        }

        return $this->response->withType('application/json')->withStringBody((string)json_encode($data ?? []));
    }
}
