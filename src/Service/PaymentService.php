<?php
declare(strict_types=1);

namespace App\Service;

/** Backend payment boundary. AzamPay credentials and the push live in fastnet_backed. */
class PaymentService
{
    public function __construct(private readonly FastnetApiClient $apiClient)
    {
    }

    public function isConfigured(): bool
    {
        // The backend owns the credentials; the web app only needs its API URL.
        return trim($this->apiClient->getBaseUrl()) !== '';
    }

    public function initiate(array $payload): ?array
    {
        $phone = $this->normalizePhone((string)($payload['payment_phone'] ?? ''));
        $bookingId = (string)($payload['booking_id'] ?? '');
        if ($phone === null || $bookingId === '') {
            return null;
        }
        try {
            $provider = $this->normalizeProvider((string)($payload['payment_method'] ?? ''));
        } catch (\InvalidArgumentException $e) {
            \Cake\Log\Log::warning('[PaymentService] Invalid provider: ' . $e->getMessage());
            return null;
        }
        // Card should not go via AzamPay — caller handles card separately
        if ($provider === 'Card') {
            return null;
        }
        // Idempotency — same booking+method+phone must not double-charge
        $idempotencyKey = hash('sha256', $bookingId . '|' . $provider . '|' . $phone);

        return $this->apiClient->post('/payments/checkout', [
            'booking_id' => $bookingId,
            'gateway' => 'AzamPay',
            'payment_method' => $provider,
            'phone_number' => $phone,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    public function status(string $paymentId, ?string $bookingId = null): ?array
    {
        // Payment status is payment-centric — never fallback to bookingId (prevents booking-success without paid)
        $paymentId = trim($paymentId);
        if ($paymentId === '') return null;
        // If caller passed card-* synthetic id, verify via bookings endpoint instead
        if (str_starts_with($paymentId, 'card-')) {
            $bid = substr($paymentId, 5);
            return $this->booking($bid);
        }
        return $this->apiClient->get('/payments/status/' . rawurlencode($paymentId));
    }

    public function booking(string $bookingId): ?array
    {
        $bookingId = trim($bookingId);
        if ($bookingId === '') return null;
        // Booking verification is booking-centric — separate endpoint, webhook is source of truth
        return $this->apiClient->get('/bookings/' . rawurlencode($bookingId));
    }

    private function normalizeProvider(string $provider): string
    {
        $p = strtolower(trim($provider));
        return match ($p) {
            'tigo', 'tigopesa', 'tigo pesa' => 'Tigo',
            'airtel', 'airtelmoney', 'airtel money' => 'Airtel',
            'halotel', 'halopesa', 'halo pesa' => 'Halopesa',
            'mpesa', 'vodacom', 'vodacom mpesa', 'm-pesa' => 'Mpesa',
            'card', 'credit', 'credit_card', 'visa', 'mastercard' => 'Card',
            default => throw new \InvalidArgumentException('Unsupported payment provider: ' . $provider),
        };
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0')) {
            $digits = '255' . substr($digits, 1);
        }
        return preg_match('/^255\d{9}$/', $digits) === 1 ? $digits : null;
    }
}
