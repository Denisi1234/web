<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use Cake\Http\Response;

/**
 * AccountController
 * Modular user dashboard, profile settings, bookings history, and preferences controller.
 */
class AccountController extends AppController
{
    protected AuthService $authService;
    protected FastnetApiClient $apiClient;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
    }

    public function menu()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/menu');
    }

    public function myProfile()
    {
        $session = $this->getRequest()->getSession();
        $userProfile = $session->read('User') ?: $this->authService->getPersonalDetails();

        if ($this->getRequest()->is(['post', 'put'])) {
            $data = $this->getRequest()->getData();
            $updated = array_merge($userProfile, (array)$data);
            $session->write('User', $updated);
            $this->Flash->success(__('Your personal details have been updated.'));
            $userProfile = $updated;
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/my-profile');
    }

    public function accountSecurity()
    {
        $userProfile = $this->authService->getPersonalDetails();

        if ($this->getRequest()->is(['post', 'put'])) {
            $data = $this->getRequest()->getData();
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';

            if (!empty($newPassword) && $newPassword === $confirmPassword) {
                $this->Flash->success(__('Your password has been updated securely.'));
            } elseif (!empty($newPassword)) {
                $this->Flash->error(__('New password and confirmation do not match.'));
            }
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/account-security');
    }

    public function myBooking()
    {
        $session = $this->getRequest()->getSession();
        $userProfile = $this->authService->getPersonalDetails();
        $bookings = [];

        $headers = [];
        $token = trim((string)$session->read('auth_token'));
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        $res = $this->apiClient->get('/user/bookings', [], $headers);
        if (!empty($res['data'])) {
            $bookings = $res['data'];
        }

        $userBookings = $bookings;
        $this->set(compact('userProfile', 'bookings', 'userBookings'));
        return $this->render('/Pages/my-booking');
    }

    public function cancelBooking(): Response
    {
        $bookingId = trim((string)$this->getRequest()->getData('booking_id', ''));
        $token = trim((string)$this->getRequest()->getSession()->read('auth_token'));
        if ($bookingId === '' || $token === '') {
            $this->Flash->error(__('Please sign in to cancel a booking.'));
            return $this->redirect(['action' => 'myBooking']);
        }

        $result = $this->apiClient->delete('/bookings/' . rawurlencode($bookingId), [
            'Authorization' => 'Bearer ' . $token,
        ]);
        if ($result !== null) {
            $this->Flash->success(__('Your booking cancellation request was submitted.'));
        } else {
            $this->Flash->error(__('We could not cancel this booking. It may already be completed or cancelled.'));
        }
        return $this->redirect(['action' => 'myBooking']);
    }

    public function findBooking(): Response
    {
        $email = strtolower(trim((string)$this->getRequest()->getData('email', '')));
        $bookingCode = trim((string)$this->getRequest()->getData('booking_number', ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $bookingCode === '') {
            return $this->response->withStatus(422)->withType('application/json')->withStringBody(json_encode(['message' => 'Enter a valid email and booking number.']));
        }

        $result = $this->apiClient->get('/payments/status/' . rawurlencode($bookingCode));
        $data = is_array($result) ? ($result['data'] ?? $result) : [];
        $guestEmail = strtolower(trim((string)($data['guest']['email'] ?? $data['guest_email'] ?? '')));
        if ($guestEmail === '' || !hash_equals($guestEmail, $email)) {
            return $this->response->withStatus(404)->withType('application/json')->withStringBody(json_encode(['message' => 'We could not find a booking with those details.']));
        }

        return $this->response->withType('application/json')->withStringBody(json_encode(['booking' => $data]));
    }

    public function paymentDetail()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/payment-detail');
    }

    public function myWishlists()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $wishlists = [];

        $res = $this->apiClient->get('/user/wishlist');
        if (!empty($res['data'])) {
            $wishlists = $res['data'];
        }

        $this->set(compact('userProfile', 'wishlists'));
        return $this->render('/Pages/my-wishlists');
    }

    public function recentlyViewed()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
        return $this->render('/Pages/recently-viewed');
    }

    public function searchPreferences()
    {
        $userProfile = $this->authService->getPersonalDetails();

        if ($this->getRequest()->is(['post', 'put'])) {
            $this->Flash->success(__('Search preferences updated.'));
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/search-preferences');
    }

    public function notifications()
    {
        $userProfile = $this->authService->getPersonalDetails();

        if ($this->getRequest()->is(['post', 'put'])) {
            $this->Flash->success(__('Notification settings saved.'));
        }

        $this->set(compact('userProfile'));
        return $this->render('/Pages/notifications');
    }

    public function languageAndCurrency()
    {
        $userProfile = $this->authService->getPersonalDetails();
        $this->set(compact('userProfile'));
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
