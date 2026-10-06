<?php
declare(strict_types=1);

namespace App\Controller\Host;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;

/**
 * HostFinanceTrait — Earnings and payout requests.
 */
trait HostFinanceTrait
{
    public function earnings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        // Finance endpoints take ~7-8s each — 90s session cache so sidebar
        // navigation stays fast; backend stays source of truth on refresh.
        $session = $this->getRequest()->getSession();
        $finance = $session->read('FinanceCache');
        $payouts = $session->read('PayoutsCache');
        $fts = (int)$session->read('FinanceCacheTs');
        if (!is_array($finance) || !is_array($payouts) || $fts < time() - 90) {
            // Drop the session lock: the 3 calls below are the slowest in
            // the portal (~7-8s each per legacy notes) and must not block
            // parallel prefetch/navigation on the session file.
            $this->releaseSession();
            // Try finance overview, fallback to admin dashboardStats (admin_owner_portal/chart-flot.php parity)
            $res = $this->apiClient->get('/finance/overview', [], $headers);
            if (empty($res) || !empty($res['_status'])) {
                $res = $this->apiClient->get('/admin/dashboard-stats', [], $headers);
            }
            if (empty($res) || !empty($res['_status'])) {
                $res = $this->apiClient->get('/admin/owners/financial-summary', [], $headers);
            }
            $finance = $res['data'] ?? $res;
            if (!is_array($finance)) $finance = [];
            // payouts ledger fallback for mobile financial_reports.dart
            $pRes = $this->apiClient->get('/payouts', [], $headers);
            $payouts = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
            if (!is_array($payouts)) $payouts = [];
            $session->write('FinanceCache', $finance);
            $session->write('PayoutsCache', $payouts);
            $session->write('FinanceCacheTs', time());
        }
        // Authoritative payout balance straight from the backend.
        // GET /payouts/summary is the same calculation POST /payouts/request
        // validates against, so the amount a host can request is guaranteed to
        // match what the backend will accept.
        $sumRes = $this->apiClient->get('/payouts/summary', [], $headers);
        $payoutSummary = $sumRes['data'] ?? $sumRes;
        if (!is_array($payoutSummary)) $payoutSummary = [];

        $this->set(compact('userProfile', 'finance', 'payouts', 'payoutSummary'));
        return $this->render('/Pages/host-earnings');
    }

    /**
     * POST /payouts/request — host asks to be paid out.
     */
    public function requestPayout(): Response
    {
        if (!$this->request->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $token = $this->rawToken();
        if ($token === '') {
            throw new UnauthorizedException(__('Sign in to request a payout.'));
        }

        $data = (array)$this->request->getData();
        $payload = [
            'amount'          => $data['amount'] ?? null,
            'payment_method'  => $data['payment_method'] ?? null,
            'account_details' => $data['account_details'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ];

        $res = $this->apiClient->post('/payouts/request', $payload, [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ]);

        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            $this->Flash->error($res['message'] ?? __('Payout request failed.'));

            return $this->response->withStatus((int)$res['_status'])->withStringBody(json_encode([
                'status'  => 'error',
                'message' => $res['message'] ?? __('Payout request failed.'),
            ]));
        }

        // Balance moved, so the cached finance figures are stale.
        $session = $this->getRequest()->getSession();
        $session->delete('FinanceCacheTs');
        $session->delete('PayoutsCache');

        $this->Flash->success(__('Payout requested. Our team will process it shortly.'));

        if ($this->request->is('json')) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'status'  => 'success',
                'message' => __('Payout requested.'),
                'payout'  => $res['payout'] ?? null,
            ]));
        }

        return $this->redirect(['controller' => 'Host', 'action' => 'earnings']);
    }

    // ---- Working-only additions mirroring admin_owner_portal ----
}
