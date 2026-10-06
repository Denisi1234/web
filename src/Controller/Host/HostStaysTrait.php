<?php
declare(strict_types=1);

namespace App\Controller\Host;

/**
 * HostStaysTrait — Bookings, check-in/out, and availability calendar.
 */
trait HostStaysTrait
{
    public function bookings()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $bookings = $this->myBookings($headers);
        $this->set(compact('userProfile', 'bookings'));
        return $this->render('/Pages/host-bookings');
    }

    /**
     * Professional arrival / departure from the host portal.
     * POST-only with CSRF + the host gate in beforeFilter; the backend
     * enforces ownership, paid-before-check-in and state order.
     */
    public function checkIn(string $id)
    {
        return $this->moveStay($id, 'check-in');
    }

    public function checkOut(string $id)
    {
        return $this->moveStay($id, 'check-out');
    }

    private function moveStay(string $id, string $move): ?\Cake\Http\Response
    {
        if (!$this->getRequest()->is('post')) {
            return $this->redirect(['action' => 'bookings']);
        }
        $id = trim($id);
        if ($id === '') {
            $this->Flash->error(__('Booking not specified.'));
            return $this->redirect(['action' => 'bookings']);
        }
        $res = $this->apiClient->post('/bookings/' . rawurlencode($id) . '/' . $move, [], $this->hostHeaders());
        if (is_array($res) && empty($res['_status']) && ($res['status'] ?? '') === 'success') {
            $this->Flash->success(__((string)($res['message'] ?? 'Done.')));
        } else {
            $this->Flash->error(__((string)($res['message'] ?? 'Could not update this booking.')));
        }
        // Refresh cached lists so the new state shows immediately.
        try {
            $session = $this->getRequest()->getSession();
            $session->delete('HostBookings');
            $session->delete('HostBookingsTs');
        } catch (\Throwable $e) {
        }
        return $this->redirect(['action' => 'bookings']);
    }

    public function calendar(?int $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $property = null;
        $rooms = [];
        if ($id) {
            // Fast path: owner-scoped list (mine=1, has host_id) instead of
            // /properties/{id} show (~7s). Falls back to show if missing.
            $property = null;
            $list = $this->myProperties($headers);
            foreach ($list as $p) {
                if ((int)($p['id'] ?? 0) === (int)$id) { $property = $p; break; }
            }
            if ($property === null) {
                $res = $this->apiClient->get('/properties/' . $id, [], $headers);
                $property = $res['data'] ?? $res;
            }
            if (empty($property) || !empty($property['_status'])) {
                $this->Flash->error(__('Property not found.'));
                return $this->redirect(['action' => 'listings']);
            }
            // Ownership guard: non-admins only manage their own lodges
            if (!$this->isAdminUser()) {
                $oid = $this->ownerId();
                $hid = (int)($property['host_id'] ?? $property['host']['id'] ?? 0);
                if ($hid > 0 && $oid > 0 && $hid !== $oid) {
                    $this->Flash->error(__('Property not found.'));
                    return $this->redirect(['action' => 'listings']);
                }
            }
            $rRes = $this->apiClient->get('/properties/' . $id . '/rooms', [], $headers);
            $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
            if (!is_array($rooms) && !empty($property['rooms'])) $rooms = $property['rooms'];
            if (!is_array($rooms)) $rooms = [];
        } else {
            // no id → redirect to listings picker
            return $this->redirect(['action' => 'listings']);
        }
        // Handle inline room price/status update (mobile calendar_pricing_editor.dart parity)
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $roomId = (int)($data['room_id'] ?? 0);
            if ($roomId > 0) {
                $payload = [];
                if (isset($data['price'])) $payload['price'] = (float)$data['price'];
                if (isset($data['customer_price'])) $payload['customer_price'] = (float)$data['customer_price'];
                if (isset($data['status'])) $payload['status'] = trim((string)$data['status']);
                if (!empty($payload)) {
                    $upRes = $this->apiClient->put('/rooms/' . $roomId, $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($upRes, '/host/calendar/' . $id)) return $bounce;
                    if (!empty($upRes['_status']) && (int)$upRes['_status'] >= 400) {
                        $this->Flash->error(__($upRes['message'] ?? 'Could not update room.'));
                    } else {
                        $this->Flash->success(__('Room updated.'));
                        return $this->redirect(['action' => 'calendar', $id]);
                    }
                }
            }
        }
        // (view vars are set below, after the availability map is built)

        // Real availability: map booked date ranges per room for the current
        // month from actual bookings. Cancelled/refunded stays do not block.
        $bookedDays = [];
        try {
            $allBookings = $this->myBookings($headers);
            $roomIds = array_map(fn($rm) => (int)($rm['id'] ?? 0), $rooms);
            $monthStart = new \DateTimeImmutable('first day of this month');
            $monthEnd = new \DateTimeImmutable('last day of this month');
            foreach ($allBookings as $b) {
                if (!is_array($b)) continue;
                $rid = (int)($b['room_id'] ?? ($b['room']['id'] ?? 0));
                if ($rid <= 0 || !in_array($rid, $roomIds, true)) continue;
                $bst = strtolower((string)($b['status'] ?? ''));
                $pst = strtolower((string)($b['payment_status'] ?? ''));
                if (in_array($bst, ['cancelled', 'canceled', 'refunded'], true)) continue;
                if ($pst !== '' && !in_array($pst, ['paid', 'pending', 'confirmed'], true)) continue;
                try {
                    $ci = new \DateTimeImmutable((string)($b['check_in'] ?? ''));
                    $co = new \DateTimeImmutable((string)($b['check_out'] ?? ''));
                } catch (\Throwable $e) {
                    continue;
                }
                if ($co <= $ci) continue;
                // Nights occupied are [check_in, check_out) — checkout day is free.
                $day = $ci > $monthStart ? $ci : $monthStart;
                $last = $co < $monthEnd->modify('+1 day') ? $co : $monthEnd->modify('+1 day');
                while ($day < $last) {
                    if ($day >= $monthStart) {
                        $bookedDays[$rid][$day->format('Y-m-d')] = true;
                    }
                    $day = $day->modify('+1 day');
                }
            }
        } catch (\Throwable $e) {
            $bookedDays = [];
        }

        $this->set(compact('userProfile', 'property', 'rooms', 'bookedDays'));
        return $this->render('/Pages/host-calendar');
    }
}
