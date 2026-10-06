<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * AdminSupportTrait — Support tickets, reviews, and lodge requests.
 */
trait AdminSupportTrait
{

    public function support()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $this->releaseSession();
            $data = (array)$this->getRequest()->getData();
            $action = trim((string)($data['action'] ?? ''));
            if ($action === 'reply' && !empty($data['ticket_id'])) {
                $tid = trim((string)$data['ticket_id']);
                $msg = trim((string)($data['message'] ?? $data['body'] ?? $data['reply'] ?? ''));
                if ($tid === '' || $msg === '') {
                    return $this->writeFail('Reply message is required.', 'support', 400);
                }
                if (strlen($msg) > 5000) {
                    return $this->writeFail('Reply is too long.', 'support', 400);
                }
                // Send both keys: backends disagree on message vs body.
                $rep = $this->apiClient->post('/tickets/' . $tid . '/reply', ['message' => $msg, 'body' => $msg], $headers);
                if ($rep === null) {
                    // Alias fallback: some backends nest replies under /support.
                    $rep = $this->apiClient->post('/support/tickets/' . $tid . '/reply', ['message' => $msg], $headers);
                }
                if ($bounce = $this->bounceOnUnauth($rep, '/admin/support')) return $bounce;
                if ($rep === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'support', 502);
                }
                if (!empty($rep['_status']) && (int)$rep['_status'] >= 400) {
                    $msgErr = (string)($rep['message'] ?? 'Could not send reply.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msgErr, (int)$rep['_status']);
                    }
                    $this->Flash->error(__($msgErr));
                } else {
                    $this->portal->clear(['_tickets', 'tickets', 'threads']);
                    return $this->writeDone('Reply sent.', 'support');
                }
            } elseif ($action === 'status' && !empty($data['ticket_id'])) {
                $tid = trim((string)$data['ticket_id']);
                $st = trim((string)($data['status'] ?? ''));
                if ($tid === '' || $st === '') {
                    return $this->writeFail('Ticket and status are required.', 'support', 400);
                }
                $allowed = ['Open', 'In Progress', 'Resolved', 'Closed'];
                $norm = null;
                foreach ($allowed as $a) {
                    if (strcasecmp($a, $st) === 0) {
                        $norm = $a;
                        break;
                    }
                }
                if ($norm === null) {
                    return $this->writeFail('Invalid ticket status.', 'support', 400);
                }
                $pat = $this->apiClient->patch('/tickets/' . $tid . '/status', ['status' => $norm], $headers);
                if ($bounce = $this->bounceOnUnauth($pat, '/admin/support')) return $bounce;
                if ($pat === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'support', 502);
                }
                if (!empty($pat['_status']) && (int)$pat['_status'] >= 400) {
                    $msgErr = (string)($pat['message'] ?? 'Could not update ticket.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msgErr, (int)$pat['_status']);
                    }
                    $this->Flash->error(__($msgErr));
                } else {
                    $this->portal->clear(['_tickets', 'tickets', 'threads']);
                    return $this->writeDone('Ticket updated.', 'support', ['status' => $norm]);
                }
            } elseif ($data !== []) {
                return $this->writeFail('Invalid support action.', 'support', 400);
            }
        }

        $this->releaseSession();
        $batch = $this->portal->getMulti([
            'tickets' => ['endpoint' => '/tickets', 'params' => [], 'ttl' => 120],
            'threads' => ['endpoint' => '/messages/threads', 'params' => [], 'ttl' => 120],
        ], $headers);
        $tRes = $batch['tickets'];
        if ($bounce = $this->bounceOnUnauth($tRes, '/admin/support')) return $bounce;
        $tickets = $tRes['data'] ?? (isset($tRes[0]) ? $tRes : []);
        if (!is_array($tickets)) $tickets = [];

        // Messages threads for admin (cached 60s)
        $mRes = $batch['threads'];
        $threads = $mRes['data'] ?? (isset($mRes[0]) ? $mRes : []);
        if (!is_array($threads)) $threads = [];

        $this->markDown($tRes, $mRes);
        $this->set(compact('userProfile', 'tickets', 'threads'));
    }

    public function reviews()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $this->releaseSession();
        $res = $this->portal->get('/admin/reviews', [], $headers, 300);
        if (empty($reviews = $res['data'] ?? null) && empty($res['_status'])) {
            // Fallback shape: some backends expose public /reviews.
            $alt = $this->portal->get('/reviews', [], $headers, 300);
            if (is_array($alt) && empty($alt['_status'])) {
                $res = $alt;
            }
        }
        $reviews = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($reviews)) $reviews = [];
        if (empty($reviews) && !empty($res) && empty($res['_status']) && isset($res['id'])) {
            $reviews = [$res];
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'reviews'));
    }


    public function lodgeRequests()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $rid = trim((string)($data['request_id'] ?? ''));
            $status = trim((string)($data['status'] ?? ''));
            if ($rid === '' || $status === '') {
                if ($this->getRequest()->is(['post','patch']) && ($data !== [])) {
                    return $this->writeFail('Request and status are required.', 'lodgeRequests', 400);
                }
            } else {
                $allowed = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
                $norm = null;
                foreach ($allowed as $a) {
                    if (strcasecmp($a, $status) === 0) {
                        $norm = $a;
                        break;
                    }
                }
                if ($norm === null) {
                    return $this->writeFail('Invalid request status.', 'lodgeRequests', 400);
                }
                $this->releaseSession();
                // Backend source of truth with alias fallback.
                $candidates = ['/lodge-requests/' . $rid . '/status', '/admin/lodge-requests/' . $rid . '/status'];
                $res = null;
                foreach ($candidates as $ep) {
                    $attempt = $this->apiClient->patch($ep, ['status' => $norm], $headers);
                    if ($attempt === null) {
                        $res = null;
                        break;
                    }
                    if ((int)($attempt['_status'] ?? 200) === 404 && $ep !== end($candidates)) {
                        continue;
                    }
                    $res = $attempt;
                    break;
                }
                if ($bounce = $this->bounceOnUnauth($res, '/admin/requests')) return $bounce;
                if ($res === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'lodgeRequests', 502);
                }
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $msg = (string)($res['message'] ?? 'Could not update request.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msg, (int)$res['_status']);
                    }
                    $this->Flash->error(__($msg));
                } else {
                    $this->portal->clear(['_lodge_requests', 'lodge-requests', 'properties', 'dashboard']);
                    return $this->writeDone('Request updated.', 'lodgeRequests', ['status' => $norm]);
                }
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));
        $type = trim((string)($q['type'] ?? ''));

        // Backend owns filtering (cached 30s per filter combo).
        $listParams = [];
        if ($search !== '') $listParams['search'] = $search;
        if ($status !== '' && $status !== 'all') $listParams['status'] = $status;
        if ($type !== '' && $type !== 'all') $listParams['type'] = $type;

        $this->releaseSession();
        $res = $this->portal->get('/lodge-requests', $listParams, $headers, 30);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/requests')) return $bounce;
        $requests = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($requests)) $requests = [];

        // Dynamic type options for filter
        $typeOptions = ['all'];
        foreach ($requests as $r) {
            $t = trim((string)($r['type'] ?? $r['room_type'] ?? ''));
            if ($t !== '' && !in_array($t, $typeOptions)) $typeOptions[] = $t;
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'requests', 'search', 'status', 'type', 'typeOptions'));
    }
}
