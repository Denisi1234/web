<?php
declare(strict_types=1);

namespace App\Controller\Bookings;

use Cake\Http\Response;

/**
 * BookingsPayStatusTrait — Payment dispatch and status polling.
 */
trait BookingsPayStatusTrait
{
    /**
     * AJAX endpoint — fires AzamPay USSD push after payment-pending page has loaded.
     * Called once by the frontend JS; idempotent via push_dispatched flag in session.
     */
    public function paymentDispatch(): Response
    {
        $this->getRequest()->allowMethod(['post']);
        $body = (array)($this->getRequest()->getBody() ? json_decode((string)$this->getRequest()->getBody(), true) : []);
        $paymentId = trim((string)($body['payment_id'] ?? $this->getRequest()->getData('payment_id', '')));
        $pending = $paymentId !== '' ? $this->getRequest()->getSession()->read('pending_payments.' . $paymentId) : null;

        if (!is_array($pending) || empty($pending['booking_id'])) {
            return $this->response->withStatus(404)->withType('application/json')
                ->withStringBody(json_encode(['ok' => false, 'error' => 'session_not_found']));
        }
        // Idempotency — do not double-dispatch
        if (!empty($pending['push_dispatched'])) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['ok' => true, 'note' => 'already_dispatched']));
        }

        // Mark dispatched before calling API (prevents double-push on network retry)
        $pending['push_dispatched'] = true;
        $this->getRequest()->getSession()->write('pending_payments.' . $paymentId, $pending);

        // Fire AzamPay USSD push (non-blocking from user perspective — page is already shown)
        $checkout = $this->paymentService->initiate([
            'booking_id'     => $pending['booking_id'],
            'amount'         => $pending['amount'] ?? 0,
            'payment_method' => $pending['payment_method'] ?? 'vodacom',
            'payment_phone'  => $pending['payment_phone'] ?? '',
            'account_name'   => $pending['account_name'] ?? '',
        ]);

        // The checkout response mints the gateway transaction id (TX-AZAM-…).
        // Keep it: status polling must use THIS id, not the synthetic session
        // key. Using the session key made every poll 404, so the page could
        // never leave "pending" even after a successful webhook payment.
        if (is_array($checkout)) {
            $txn = trim((string)(
                $checkout['transaction_id']
                ?? $checkout['data']['transaction_id']
                ?? ''
            ));
            if ($txn !== '') {
                $pending['gateway_txn'] = $txn;
                $this->getRequest()->getSession()->write('pending_payments.' . $paymentId, $pending);
            }
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['ok' => true]));
    }

    public function paymentStatus(): Response
    {
        $session = $this->getRequest()->getSession();
        $paymentId = trim((string)$this->getRequest()->getQuery('payment_id', ''));
        $pending = $paymentId !== '' ? $session->read('pending_payments.' . $paymentId) : null;
        if (!is_array($pending)) {
            return $this->response->withStatus(404)->withType('application/json')->withStringBody(json_encode(['status' => 'expired']));
        }

        // Terminal states are latched on first observation. Without this a
        // gateway status string we fail to recognise later in the flow can
        // drag a settled payment back to "pending" and the page then tells the
        // guest their money was never received.
        $terminal = ['paid', 'failed', 'expired', 'review'];
        $latched = (string)($pending['status'] ?? '');
        if ($latched !== '' && in_array($latched, $terminal, true)) {
            return $this->paymentStatusResponse($latched, (int)($pending['booking_id'] ?? 0), $pending['payment_status'] ?? null);
        }

        $result = $this->paymentService->status(
            // Prefer the gateway transaction id minted by checkout; the
            // synthetic session key never resolves server-side.
            (string)($pending['gateway_txn'] ?? $paymentId),
            (string)$pending['booking_id']
        );
        $data = is_array($result) ? ($result['data'] ?? $result) : [];
        $raw = strtolower((string)($data['status'] ?? ($data['payment_status'] ?? ($data['transactionStatus'] ?? ($data['paymentStatus'] ?? 'pending')))));

        $normalized = match ($raw) {
            'paid', 'success', 'successful', 'completed', 'confirmed', 'settled', 'authorized', '00' => 'paid',
            'failed', 'cancelled', 'canceled', 'declined', 'rejected', 'error' => 'failed',
            'expired', 'timeout', 'timed_out' => 'expired',
            // Money arrived but the amount did not match. Not a retry - a human
            // has to reconcile it, so surface it instead of looping to timeout.
            'amount_mismatch' => 'review',
            default => 'pending',
        };

        if ($normalized !== 'pending') {
            $session->write('pending_payments.' . $paymentId . '.status', $normalized);
        }
        if ($normalized === 'paid') {
            // Paid is the point of no return for the dashboard: persist the
            // verified booking into the dashboard session BEFORE dropping the
            // pending entry, so /my-booking shows it even when the backend
            // list lags or the guest paid with a different email / no token.
            $this->rememberPaidBookingForDashboard(
                (string)($pending['booking_id'] ?? ''),
                (string)($pending['guest_email'] ?? ''),
                $pending
            );
            $session->delete('pending_payments.' . $paymentId);
            $this->forgetPendingPayment($paymentId);
        } elseif (in_array($normalized, ['failed', 'expired'], true)) {
            $this->forgetPendingPayment($paymentId);
        }

        return $this->paymentStatusResponse(
            $normalized,
            (int)$pending['booking_id'],
            $raw,
            // The success page needs the booking email to verify a guest
            // booking that has no session. Without it every paid guest 404s.
            (string)($pending['guest_email'] ?? '')
        );
    }

    private function paymentStatusResponse(string $status, int $bookingId, ?string $raw, string $email = ''): Response
    {
        return $this->response->withType('application/json')->withStringBody(json_encode([
            'status' => $status,
            'booking_id' => $bookingId,
            'gateway_status' => $raw,
            'email' => $email,
        ]));
    }
}
