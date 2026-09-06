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
        if ($phone === null || (string)($payload['booking_id'] ?? '') === '') {
            return null;
        }

        return $this->apiClient->post('/payments/checkout', [
            'booking_id' => (string)$payload['booking_id'],
            'gateway' => 'AzamPay',
            'payment_method' => $this->normalizeProvider((string)($payload['payment_method'] ?? '')),
            'phone_number' => $phone,
        ]);
    }

    public function status(string $paymentId, ?string $bookingId = null): ?array
    {
        $lookup = trim($bookingId ?? '') !== '' ? $bookingId : $paymentId;
        return $lookup === '' ? null : $this->apiClient->get('/payments/status/' . rawurlencode($lookup));
    }

    public function booking(string $bookingId): ?array
    {
        return $bookingId === '' ? null : $this->apiClient->get('/payments/status/' . rawurlencode($bookingId));
    }

    private function normalizeProvider(string $provider): string
    {
        return match (strtolower(trim($provider))) {
            'tigo', 'tigopesa', 'tigo pesa' => 'Tigo',
            'airtel', 'airtelmoney', 'airtel money' => 'Airtel',
            'halotel', 'halopesa', 'halo pesa' => 'Halopesa',
            default => 'Mpesa',
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
