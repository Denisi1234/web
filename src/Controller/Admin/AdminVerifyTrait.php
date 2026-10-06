<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * AdminVerifyTrait — Owner and lodge verification decisions.
 */
trait AdminVerifyTrait
{
    public function verify(string $type = '', string $id = '')
    {
        if ($r = $this->requireAdmin()) return $r;
        if (!$this->getRequest()->is('post')) {
            if ($this->wantsJson()) {
                return $this->jsonErr('Method not allowed.', 405);
            }
            return $this->redirect(['action' => 'owners']);
        }
        $headers = $this->hostHeaders();
        $data = (array)$this->getRequest()->getData();
        $status = trim((string)($data['status'] ?? 'approved'));
        $reason = trim((string)($data['reason'] ?? $data['admin_notes'] ?? ''));
        $type = strtolower(trim($type));
        $id = trim($id);
        $isLodge = $type === 'lodge' || $type === 'property';

        // Backend ids are numeric today but may be UUIDs tomorrow — accept
        // anything sane. The old ctype_digit() check rejected every UUID row,
        // making ALL approve/reject buttons on those rows dead with a cryptic
        // "Invalid record id." flash that looked like trash UI.
        if ($id === '' || !preg_match('/^[A-Za-z0-9:_-]{1,64}$/', $id)) {
            return $this->writeFail('Invalid record id.', $isLodge ? 'lodges' : 'owners', 400);
        }

        // Backend validation:
        // owner: approved,rejected,changes_requested,suspended
        // lodge: Active,Pending,Removed,changes_requested,rejected
        if ($isLodge) {
            $map = [
                'approved' => 'Active',
                'approve' => 'Active',
                'active' => 'Active',
                'rejected' => 'rejected',
                'reject' => 'rejected',
                'changes_requested' => 'changes_requested',
                'pending' => 'Pending',
                'removed' => 'Removed',
                'suspended' => 'Removed',
            ];
            $low = strtolower($status);
            if (!isset($map[$low])) {
                // Never default an unknown value to an approval.
                return $this->writeFail('Unknown verification status.', 'lodges', 422);
            }
            $status = $map[$low];
        } else {
            // owner: normalise to backend lowercase set
            $low = strtolower($status);
            $allowedOwner = ['approved', 'rejected', 'changes_requested', 'suspended'];
            if (!in_array($low, $allowedOwner, true)) {
                return $this->writeFail('Unknown verification status.', 'owners', 422);
            }
            $status = $low;
            $type = 'owner';
        }
        if (($status === 'rejected' || $status === 'changes_requested') && $reason === '' && $this->wantsJson()) {
            return $this->jsonErr('A reason is required for this decision.', 422);
        }
        // Send every reason key the backend has ever accepted (reason,
        // admin_notes, notes). Older rows failed because the direct-JS path
        // sent only `reason` while some backend builds validate `admin_notes`,
        // and vice versa — both paths now send identical payloads.
        $payload = ['status' => $status];
        if ($reason !== '') {
            $payload['reason'] = $reason;
            $payload['admin_notes'] = $reason;
            $payload['notes'] = $reason;
        }

        // Release the session lock: the write below is the slowest part and
        // must not serialize parallel PJAX/prefetch on the session file.
        $this->releaseSession();
        // One real route: POST /admin/verification/{owner|lodge}/{id}
        // (fastnet_backend routes/api.php). The old path matrix retried 404s
        // against /verification/lodge/{id} — the HOST submission endpoint —
        // which could re-submit a lodge instead of reporting "not found".
        $endpoint = ($isLodge ? '/admin/verification/lodge/' : '/admin/verification/owner/') . $id;
        $res = $this->apiClient->post($endpoint, $payload, $headers);
        $back = $isLodge ? '/admin/lodges' : '/admin/owners';
        if ($bounce = $this->bounceOnUnauth($res, $back)) return $bounce;
        if ($res === null) {
            return $this->writeFail('Service unavailable. Please try again.', $isLodge ? 'lodges' : 'owners', 502);
        }
        if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
            $code = (int)$res['_status'];
            $msg = (string)($res['message'] ?? 'Verification failed.');
            if ($code >= 500) {
                // Backend itself is failing — say so plainly with NOT-saved,
                // or the admin will keep clicking a dead button thinking the
                // portal is trash. Never pretend success on 5xx.
                $msg = sprintf('Backend error (%d) — decision NOT saved. %s', $code, $msg);
            }
            if ($this->wantsJson()) {
                return $this->jsonErr($msg, $code);
            }
            $this->Flash->error(__($msg));
        } else {
            // Scoped bust only: a lodge decision must not cold the whole portal.
            $this->portal->clear($isLodge ? ['properties', 'dashboard', '_verif'] : ['users', 'owners', 'dashboard', '_verif']);
            return $this->writeDone('Verification updated.', $isLodge ? 'lodges' : 'owners', ['status' => $status]);
        }
        if ($isLodge) {
            return $this->redirect(['action' => 'lodges']);
        }
        return $this->redirect(['action' => 'owners']);
    }
}
