<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * AdminFinanceTrait — Finance overview, ledger, and payouts.
 */
trait AdminFinanceTrait
{

    public function financeOverview()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $q = $this->getRequest()->getQueryParams();
        $dateRange = trim((string)($q['date_range'] ?? '30_days'));
        $params = ['date_range' => $dateRange];
        if (!empty($q['date_from'])) $params['date_from'] = $q['date_from'];
        if (!empty($q['date_to'])) $params['date_to'] = $q['date_to'];

        $this->releaseSession();
        $res = $this->portal->get('/finance/overview', $params, $headers, 300);
        if (empty($res) || !empty($res['_status'])) {
            $res = $this->portal->get('/admin/dashboard-stats', $params, $headers, 300);
        }
        $finance = $res['data'] ?? $res;
        if (!is_array($finance)) $finance = [];

        $this->markDown($res);
        $this->set(compact('userProfile', 'finance', 'dateRange'));
    }

    public function financeLedger()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $q = $this->getRequest()->getQueryParams();
        $params = [];
        foreach (['search','transaction_id','booking_reference','min_amount','max_amount','date_from','date_to','owner_id','property_id','transaction_type','payment_status','payout_status','sort_by','page','per_page'] as $k) {
            if (isset($q[$k]) && $q[$k] !== '') $params[$k] = $q[$k];
        }
        if (empty($params['per_page'])) $params['per_page'] = 15;
        if (empty($params['page'])) $params['page'] = 1;

        $this->releaseSession();
        $res = $this->portal->get('/finance/ledger', $params, $headers, 60);
        $ledger = $res['data'] ?? $res;
        if (!is_array($ledger)) $ledger = [];
        $transactions = $ledger['data'] ?? $ledger;
        if (!is_array($transactions)) $transactions = [];

        // Keep raw for pagination meta
        $meta = $ledger['meta'] ?? null;

        $this->markDown($res);
        $this->set(compact('userProfile', 'transactions', 'ledger', 'meta', 'params'));
    }

    public function payouts()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            // Handle payout status update via same page POST
            if (!empty($data['payout_id']) && !empty($data['status'])) {
                $id = trim((string)$data['payout_id']);
                $status = strtoupper(trim((string)$data['status']));
                $allowed = ['REQUESTED', 'PROCESSING', 'PAID', 'FAILED'];
                if ($id === '' || !in_array($status, $allowed, true)) {
                    return $this->writeFail('Invalid payout update.', 'payouts', 400);
                }
                $notes = trim((string)($data['notes'] ?? ''));
                $payload = ['status' => $status];
                if ($notes !== '') {
                    $payload['notes'] = $notes;
                    $payload['admin_notes'] = $notes;
                }
                $this->releaseSession();
                $candidates = ['/payouts/' . $id . '/status', '/admin/payouts/' . $id . '/status'];
                $up = null;
                foreach ($candidates as $ep) {
                    $attempt = $this->apiClient->patch($ep, $payload, $headers);
                    if ($attempt === null) {
                        $up = null;
                        break;
                    }
                    if ((int)($attempt['_status'] ?? 200) === 404 && $ep !== end($candidates)) {
                        continue;
                    }
                    $up = $attempt;
                    break;
                }
                if ($bounce = $this->bounceOnUnauth($up, '/admin/finance/payouts')) return $bounce;
                if ($up === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'payouts', 502);
                }
                if (!empty($up['_status']) && (int)$up['_status'] >= 400) {
                    $msg = (string)($up['message'] ?? 'Could not update payout.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msg, (int)$up['_status']);
                    }
                    $this->Flash->error(__($msg));
                } else {
                    $this->portal->clear(['_payouts', 'payouts', 'finance', 'dashboard']);
                    return $this->writeDone('Payout updated.', 'payouts', ['status' => $status]);
                }
            } elseif ($data !== []) {
                return $this->writeFail('Payout and status are required.', 'payouts', 400);
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $status = trim((string)($q['status'] ?? ''));
        $params = [];
        if ($status !== '') $params['status'] = $status;

        $this->releaseSession();
        $batch = $this->portal->getMulti([
            'payouts' => ['endpoint' => '/payouts', 'params' => $params, 'ttl' => 60],
            'fin' => ['endpoint' => '/finance/overview', 'params' => [], 'ttl' => 300],
        ], $headers);
        $res = $batch['payouts'];
        if ($bounce = $this->bounceOnUnauth($res, '/admin/finance/payouts')) return $bounce;
        $payouts = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($payouts)) $payouts = [];

        // Also fetch finance overview for cards (cached 120s)
        $fin = $batch['fin'];
        $finance = $fin['data'] ?? $fin;
        if (!is_array($finance)) $finance = [];

        $this->markDown($res, $fin);
        $this->set(compact('userProfile', 'payouts', 'finance', 'status'));
    }
}
