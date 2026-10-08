<?php
declare(strict_types=1);

namespace App\Controller\Bookings;

/**
 * BookingsVerifyTrait — public receipt-QR verification page.
 *
 * Opened by any phone camera from a printed confirmation
 * (`/verify-booking/{code}?s={signature}`). Public by design: the HMAC
 * signature is the auth, and the backend only returns the slim receipt
 * mirror (no guest contact details, no payment internals). Never
 * fabricated: backend failure renders the retry state, not a verdict.
 */
trait BookingsVerifyTrait
{
    public function verifyReceipt(string $code = '')
    {
        $code = trim($code);
        $sig = trim((string)($this->getRequest()->getQuery('s') ?? ''));
        $valid = false;
        $booking = null;
        $error = '';
        if ($code === '' || $sig === '') {
            $error = 'This confirmation link is incomplete. Please scan the QR code on your receipt again.';
        } else {
            try {
                $res = $this->apiClient->get('/bookings/verify', ['code' => $code, 's' => $sig], [], 8);
                if (is_array($res) && array_key_exists('valid', $res)) {
                    $valid = !empty($res['valid']);
                    $booking = is_array($res['booking'] ?? null) ? $res['booking'] : null;
                    if (!$valid) {
                        $error = trim((string)($res['message'] ?? 'This confirmation could not be verified.'));
                    }
                } else {
                    $error = 'Verification service is unreachable right now. Please try again in a moment.';
                }
            } catch (\Throwable $e) {
                $error = 'Verification service is unreachable right now. Please try again in a moment.';
            }
        }
        $this->set(compact('code', 'valid', 'booking', 'error'));
        return $this->render('/Pages/verify-booking');
    }
}
