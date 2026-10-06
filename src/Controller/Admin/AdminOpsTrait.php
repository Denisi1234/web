<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * AdminOpsTrait — Bookings and staff management.
 */
trait AdminOpsTrait
{

    public function bookings()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $bid = trim((string)($data['booking_id'] ?? ''));
            $status = trim((string)($data['status'] ?? ''));
            if ($bid === '' || $status === '') {
                return $this->writeFail('Booking and status are required.', 'bookings', 400);
            }
            $allowed = ['Pending', 'Confirmed', 'Checked In', 'Completed', 'Cancelled'];
            $norm = null;
            foreach ($allowed as $a) {
                if (strcasecmp($a, $status) === 0) {
                    $norm = $a;
                    break;
                }
            }
            if ($norm === null) {
                return $this->writeFail('Invalid booking status.', 'bookings', 400);
            }
            // Release the session lock before slow backend I/O.
            $this->releaseSession();
            // Backend source of truth with alias fallback.
            $candidates = ['/admin/bookings/' . $bid . '/status', '/bookings/' . $bid . '/status'];
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
            if ($bounce = $this->bounceOnUnauth($res, '/admin/bookings')) return $bounce;
            if ($res === null) {
                return $this->writeFail('Service unavailable. Please try again.', 'bookings', 502);
            }
            if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                $msg = (string)($res['message'] ?? 'Could not update booking.');
                if ($this->wantsJson()) {
                    return $this->jsonErr($msg, (int)$res['_status']);
                }
                $this->Flash->error(__($msg));
            } else {
                $this->portal->clear(['_bookings', 'bookings', 'finance', 'dashboard']);
                return $this->writeDone('Booking status updated.', 'bookings', ['status' => $norm]);
            }
        }

        $q = $this->getRequest()->getQueryParams();
        $search = trim((string)($q['search'] ?? ''));
        $status = trim((string)($q['status'] ?? ''));

        // Backend owns filtering — search/status go to the API (cached 30s).
        $listParams = [];
        if ($search !== '') $listParams['search'] = $search;
        if ($status !== '' && $status !== 'all') $listParams['status'] = $status;

        $this->releaseSession();
        $res = $this->portal->get('/admin/bookings', $listParams, $headers, 30);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/bookings')) return $bounce;
        $bookings = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($bookings)) $bookings = [];
        // Fallback to generic bookings
        if (empty($bookings) && !empty($res['_status'])) {
            $r2 = $this->portal->get('/bookings', $listParams, $headers, 30);
            $bookings = $r2['data'] ?? (isset($r2[0]) ? $r2 : []);
            if (!is_array($bookings)) $bookings = [];
        }

        $this->markDown($res);
        $this->set(compact('userProfile', 'bookings', 'search', 'status'));
    }

    public function staff()
    {
        if ($r = $this->requireAdmin()) return $r;
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        if ($this->getRequest()->is(['post','patch','put','delete'])) {
            $this->releaseSession();
            $data = (array)$this->getRequest()->getData();
            // Native DELETE (direct path) carries no action field.
            $method = strtolower($this->getRequest()->getMethod());
            $action = trim((string)($data['action'] ?? ''));
            if ($action === '' && $method === 'delete') {
                $action = 'delete';
            }
            if ($action === 'add' || $action === 'update') {
                $payload = [
                    'name' => trim((string)($data['name'] ?? '')),
                    'role' => trim((string)($data['role'] ?? 'Receptionist')),
                    'phone' => trim((string)($data['phone'] ?? '')),
                ];
                $allowedRoles = ['Manager', 'Receptionist', 'Housekeeper', 'Maintenance'];
                if (!in_array($payload['role'], $allowedRoles, true)) {
                    $payload['role'] = 'Receptionist';
                }
                if ($payload['name'] === '' || $payload['phone'] === '') {
                    return $this->writeFail('Name and phone are required.', 'staff', 400);
                }
                if (strlen($payload['phone']) < 7) {
                    return $this->writeFail('Enter a valid phone number.', 'staff', 400);
                }
                if ($action === 'update' && !empty($data['staff_id'])) {
                    $sid = trim((string)$data['staff_id']);
                    if ($sid === '') {
                        return $this->writeFail('Staff id is required.', 'staff', 400);
                    }
                    $res = $this->apiClient->patch('/staff/' . $sid, $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                    if ($res === null) {
                        return $this->writeFail('Service unavailable. Please try again.', 'staff', 502);
                    }
                    if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                        $msg = (string)($res['message'] ?? 'Could not update staff.');
                        if ($this->wantsJson()) {
                            return $this->jsonErr($msg, (int)$res['_status']);
                        }
                        $this->Flash->error(__($msg));
                    } else {
                        $this->portal->clear(['_staff', 'staff']);
                        return $this->writeDone('Staff updated.', 'staff');
                    }
                } else {
                    $res = $this->apiClient->post('/staff', $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                    if ($res === null) {
                        return $this->writeFail('Service unavailable. Please try again.', 'staff', 502);
                    }
                    if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                        $msg = (string)($res['message'] ?? 'Could not add staff.');
                        if ($this->wantsJson()) {
                            return $this->jsonErr($msg, (int)$res['_status']);
                        }
                        $this->Flash->error(__($msg));
                    } else {
                        $this->portal->clear(['_staff', 'staff']);
                        return $this->writeDone('Staff added.', 'staff');
                    }
                }
            } elseif ($action === 'delete' && !empty($data['staff_id'])) {
                $sid = trim((string)$data['staff_id']);
                if ($sid === '') {
                    return $this->writeFail('Staff id is required.', 'staff', 400);
                }
                $res = $this->apiClient->delete('/staff/' . $sid, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
                if ($res === null) {
                    return $this->writeFail('Service unavailable. Please try again.', 'staff', 502);
                }
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $msg = (string)($res['message'] ?? 'Could not delete staff.');
                    if ($this->wantsJson()) {
                        return $this->jsonErr($msg, (int)$res['_status']);
                    }
                    $this->Flash->error(__($msg));
                } else {
                    $this->portal->clear(['_staff', 'staff']);
                    return $this->writeDone('Staff deleted.', 'staff');
                }
            } elseif ($action !== '') {
                return $this->writeFail('Unknown staff action.', 'staff', 400);
            }
        }

        $this->releaseSession();
        $res = $this->portal->get('/staff', [], $headers, 180);
        if ($bounce = $this->bounceOnUnauth($res, '/admin/staff')) return $bounce;
        $staff = $res['data'] ?? (isset($res[0]) ? $res : []);
        if (!is_array($staff)) $staff = [];

        $this->markDown($res);
        $this->set(compact('userProfile', 'staff'));
    }
}
