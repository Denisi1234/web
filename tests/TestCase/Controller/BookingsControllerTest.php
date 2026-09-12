<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * BookingsControllerTest — quote expiry & payment verification
 */
class BookingsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testBookingPageRequiresValidQuoteForPayment(): void
    {
        // Direct GET to payment step without quote must redirect to bookingPage (no fabricated price)
        $this->get('/bookingpage-03');
        $this->assertResponseCode(302);
        $this->assertRedirectContains('/booking-page');
    }

    public function testQuoteExpiryIsEnforced(): void
    {
        // Simulate expired quote in session
        $this->session(['booking_quotes.expired123' => [
            'quote_id' => 'expired123',
            'expires_at' => time() - 100,
            'property_id' => 1,
            'room_id' => 1,
            'check_in' => date('Y-m-d', strtotime('+1 day')),
            'check_out' => date('Y-m-d', strtotime('+2 days')),
            'adults' => 2, 'children' => 0, 'rooms' => 1,
            'calculation' => ['total_amount' => 100, 'subtotal' => 80, 'taxes' => 20],
        ]]);
        $this->post('/bookingpage-02', [
            'quote_id' => 'expired123',
            'payment_method' => 'vodacom',
            'payment_phone' => '255712345678',
            '_csrfToken' => $this->_getCsrfToken(),
        ]);
        // Should redirect back to bookingPage with flash about expiry, not create booking
        $this->assertResponseCode(302);
    }

    private function _getCsrfToken(): string
    {
        $this->get('/booking-page');
        $token = $this->_request->getAttribute('csrfToken') ?? '';
        if (empty($token) && isset($this->_response)) {
            // fallback from cookie
            $cookies = $this->_response->getCookie('csrfToken');
            $token = $cookies['value'] ?? '';
        }
        return (string)$token;
    }
}
