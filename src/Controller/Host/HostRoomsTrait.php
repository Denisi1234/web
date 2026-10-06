<?php
declare(strict_types=1);

namespace App\Controller\Host;

/**
 * HostRoomsTrait — Room and lodge management.
 */
trait HostRoomsTrait
{
    public function rooms()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $search = trim((string)$this->getRequest()->getQuery('search', ''));
        $status = trim((string)$this->getRequest()->getQuery('status', ''));

        // Fetch properties for owner filter + dropdown (owner-scoped)
        $properties = $this->myProperties($headers);

        // Fetch all rooms
        $rRes = $this->apiClient->get('/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];
        // Fallback per-property only if /rooms empty — capped at 3 to avoid
        // N+1 fan-out (~1.9s each) blocking sidebar navigation.
        // Skipped when /rooms itself failed transport (null): the backend is
        // down, so per-property calls would just burn 3 more timeouts.
        if (empty($rooms) && $rRes !== null && !empty($properties)) {
            $agg = [];
            foreach (array_slice($properties, 0, 3) as $p) {
                $pid = $p['id'] ?? null;
                if (!$pid) continue;
                $rr = $this->apiClient->get('/properties/' . $pid . '/rooms', [], $headers);
                $list = $rr['data'] ?? (isset($rr[0]) ? $rr : []);
                if (is_array($list)) $agg = array_merge($agg, $list);
            }
            if (!empty($agg)) $rooms = $agg;
        }

        // Search filter (client-side parity room-list.php:search)
        if ($search !== '') {
            $low = strtolower($search);
            $rooms = array_values(array_filter($rooms, function ($r) use ($low, $properties) {
                $fields = [
                    strtolower((string)($r['room_number'] ?? $r['name'] ?? '')),
                    strtolower((string)($r['room_type'] ?? $r['type'] ?? '')),
                    strtolower((string)($r['floor'] ?? '')),
                    strtolower((string)($r['status'] ?? '')),
                ];
                // enrich with property name/city
                $pid = $r['property_id'] ?? $r['property']['id'] ?? null;
                if ($pid) {
                    foreach ($properties as $p) {
                        if ((int)$p['id'] === (int)$pid) {
                            $fields[] = strtolower((string)($p['name'] ?? ''));
                            $fields[] = strtolower((string)($p['city'] ?? ''));
                            break;
                        }
                    }
                }
                foreach ($fields as $f) if (str_contains($f, $low)) return true;
                return false;
            }));
        }
        if ($status !== '' && $status !== 'all') {
            $rooms = array_values(array_filter($rooms, fn($r) => strtolower((string)($r['status'] ?? '')) === strtolower($status)));
        }

        $this->set(compact('userProfile', 'properties', 'rooms', 'search', 'status'));
        return $this->render('/Pages/host-rooms');
    }

    /**
     * Normalize amenity input: form may send a single comma-joined string
     * ("Wifi, AC, TV") or an array — backend expects a clean string array.
     */
    private function amenityList(mixed $raw): array
    {
        $out = [];
        foreach ((array)$raw as $item) {
            foreach (explode(',', (string)$item) as $part) {
                $part = trim($part);
                if ($part !== '') $out[] = $part;
            }
        }
        return array_values(array_unique($out));
    }

    private function roomErrorMessage(mixed $res, string $default = 'An error occurred.'): string
    {
        if (!is_array($res)) return $default;
        $msg = trim((string)($res['message'] ?? $default));
        if (!empty($res['errors']) && is_array($res['errors'])) {
            $flat = [];
            foreach ($res['errors'] as $fieldErrors) {
                foreach ((array)$fieldErrors as $e) {
                    if (is_string($e) && trim($e) !== '') $flat[] = trim($e);
                }
            }
            if (!empty($flat)) $msg .= ': ' . implode(' ', array_slice($flat, 0, 3));
        }
        return $msg;
    }

    public function addRoom()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();

        $properties = $this->myProperties($headers);

        // Preselect a property arriving from onboarding (?property_id=)
        $wantPid = (int)$this->getRequest()->getQuery('property_id', 0);
        if ($wantPid > 0) {
            usort($properties, fn($a, $b) => ((int)($b['id'] ?? 0) === $wantPid) <=> ((int)($a['id'] ?? 0) === $wantPid));
        }

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $propertyId = (int)($data['property_id'] ?? $this->getRequest()->getQuery('property_id', 0));
            if ($propertyId <= 0 && !empty($properties)) $propertyId = (int)($properties[0]['id'] ?? 0);
            if ($propertyId <= 0) {
                $this->Flash->error(__('No property available. Create a property first.'));
            } else {
                $price = (float)($data['price'] ?? $data['price_per_night'] ?? $data['customer_price'] ?? 0);
                $cap = (int)($data['capacity'] ?? $data['max_adults'] ?? 1);
                $payload = [
                    'property_id' => $propertyId,
                    'room_number' => trim((string)($data['room_number'] ?? '')),
                    'room_type' => trim((string)($data['room_type'] ?? 'Standard')),
                    'price' => $price,
                    'price_per_night' => $price,
                    'customer_price' => $price,
                    'capacity' => $cap,
                    'max_adults' => (int)($data['max_adults'] ?? $cap),
                    'max_children' => (int)($data['max_children'] ?? 0),
                    'bed_configuration' => trim((string)($data['bed_configuration'] ?? '')),
                    'number_of_beds' => (int)($data['number_of_beds'] ?? 1),
                    'floor' => trim((string)($data['floor'] ?? '')),
                    'room_size' => trim((string)($data['room_size'] ?? '')),
                    'status' => trim((string)($data['status'] ?? 'available')),
                    'description' => trim((string)($data['description'] ?? '')),
                    'amenities' => $this->amenityList($data['amenities'] ?? ($data['amenities_raw'] ?? [])),
                    'photos' => array_values(array_filter(array_map('trim', (array)($data['photos'] ?? [])))),
                ];
                if ($payload['room_number'] === '' || $payload['price'] <= 0) {
                    $this->Flash->error(__('Room number and price are required.'));
                } else {
                    $res = $this->apiClient->post('/properties/' . $propertyId . '/rooms', $payload, $headers);
                    if ($bounce = $this->bounceOnUnauth($res, '/host/rooms/add')) return $bounce;
                    if (empty($res) || (!empty($res['_status']) && (int)$res['_status'] >= 400)) {
                        $altRes = $this->apiClient->post('/rooms', $payload, $headers);
                        if (!empty($altRes) && (empty($altRes['_status']) || (int)$altRes['_status'] < 400)) {
                            $res = $altRes;
                        }
                    }
                    if (empty($res) || (!empty($res['_status']) && (int)$res['_status'] >= 400)) {
                        $this->Flash->error(__($this->roomErrorMessage($res, 'Could not create room.')));
                    } else {
                        $this->Flash->success(__('Room created.'));
                        return $this->redirect(['action' => 'rooms']);
                    }
                }
            }
        }

        $isEdit = false;
        $room = null;
        $this->set(compact('userProfile', 'properties', 'room', 'isEdit'));
        return $this->render('/Pages/host-room-form');
    }

    public function editRoom(?string $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $roomId = (int)$id;
        if ($roomId <= 0) return $this->redirect(['action' => 'rooms']);

        // Fetch room
        $rRes = $this->apiClient->get('/rooms/' . $roomId, [], $headers);
        $room = $rRes['data'] ?? $rRes;
        if (empty($room) || !empty($room['_status']) || empty($room['id'])) {
            $this->Flash->error(__('Room not found.'));
            return $this->redirect(['action' => 'rooms']);
        }

        $properties = $this->myProperties($headers);

        if ($this->getRequest()->is(['post','put','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $payload = [];
            foreach (['room_number','room_type','type','price','price_per_night','customer_price','capacity','max_adults','max_children','bed_configuration','number_of_beds','floor','room_size','status','description'] as $k) {
                if (isset($data[$k])) {
                    if (in_array($k, ['price','price_per_night','customer_price','capacity','max_adults','max_children','number_of_beds'])) {
                        $payload[$k === 'type' ? 'room_type' : $k] = is_numeric($data[$k]) ? (float)$data[$k] : trim((string)$data[$k]);
                    } else {
                        $payload[$k === 'type' ? 'room_type' : $k] = trim((string)$data[$k]);
                    }
                }
            }
            if (isset($payload['price'])) {
                $payload['price_per_night'] = $payload['price'];
                $payload['customer_price'] = $payload['price'];
            }
            if (isset($data['property_id'])) {
                $payload['property_id'] = (int)$data['property_id'];
            }
            if (isset($data['amenities']) || isset($data['amenities_raw'])) {
                $payload['amenities'] = $this->amenityList($data['amenities'] ?? ($data['amenities_raw'] ?? []));
            }
            if (isset($data['photos'])) $payload['photos'] = array_values(array_filter(array_map('trim', (array)$data['photos'])));

            $res = $this->apiClient->put('/rooms/' . $roomId, $payload, $headers);
            if ($bounce = $this->bounceOnUnauth($res, '/host/rooms/' . $roomId)) return $bounce;
            if (empty($res) || (!empty($res['_status']) && (int)$res['_status'] >= 400)) {
                $this->Flash->error(__($this->roomErrorMessage($res, 'Could not update room.')));
            } else {
                $this->Flash->success(__('Room updated.'));
                return $this->redirect(['action' => 'rooms']);
            }
        }

        $isEdit = true;
        $this->set(compact('userProfile', 'properties', 'room', 'isEdit'));
        return $this->render('/Pages/host-room-form');
    }

    public function editLodge(?string $id = null)
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $propId = $id !== null ? (int)$id : null;

        $properties = $this->myProperties($headers);

        $property = null;
        if ($propId) {
            foreach ($properties as $p) {
                if ((int)($p['id'] ?? 0) === (int)$propId) { $property = $p; break; }
            }
            if ($property === null) {
                $r = $this->apiClient->get('/properties/' . $propId, [], $headers);
                $property = $r['data'] ?? $r;
                if (empty($property) || !empty($property['_status'])) $property = null;
            }
        }
        if (!$property && !empty($properties)) $property = $properties[0];
        if (!$property) {
            $this->Flash->error(__('No property found. Create one first.'));
            return $this->redirect(['action' => 'listings']);
        }
        // Ownership guard for explicit ids
        if ($propId && !$this->isAdminUser()) {
            $oid = $this->ownerId();
            $hid = (int)($property['host_id'] ?? $property['host']['id'] ?? 0);
            if ($hid > 0 && $oid > 0 && $hid !== $oid) {
                $this->Flash->error(__('Property not found.'));
                return $this->redirect(['action' => 'listings']);
            }
        }
        $propId = (int)($property['id'] ?? $propId);

        // Wizard step for the template (1 Basics → 2 Location → 3 Photos → 4 Review).
        $step = (int)$this->getRequest()->getQuery('step', 1);
        if ($step < 1 || $step > 4) $step = 1;

        // Aggregate room types/amenities for chips
        $rRes = $this->apiClient->get('/properties/' . $propId . '/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];

        if ($this->getRequest()->is(['post','put','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $payload = [
                'name' => trim((string)($data['name'] ?? $property['name'] ?? '')),
                'description' => trim((string)($data['description'] ?? $property['description'] ?? '')),
                'address' => trim((string)($data['address'] ?? $property['address'] ?? '')),
                'city' => trim((string)($data['city'] ?? $property['city'] ?? '')),
                'area' => trim((string)($data['area'] ?? $property['area'] ?? '')),
                'price_per_night' => (float)($data['price_per_night'] ?? $property['price_per_night'] ?? 0),
                'image_url' => trim((string)($data['image_url'] ?? $property['image_url'] ?? '')),
                'amenities' => $this->amenityList($data['amenities'] ?? $property['amenities'] ?? []),
            ];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Name and price are required.'));
            } else {
                $res = $this->apiClient->put('/properties/' . $propId, $payload, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/host/lodge/' . $propId . '/edit')) return $bounce;
                if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update lodge.'));
                } else {
                    $this->clearHostPropertiesCache();
                    $this->Flash->success(__('Lodge updated.'));
                    return $this->redirect(['action' => 'listings']);
                }
            }
        }

        $this->set(compact('userProfile', 'property', 'rooms', 'step'));
        if ($step === 2) {
            $pLat = is_numeric($property['latitude'] ?? $property['lat'] ?? null) ? (float)($property['latitude'] ?? $property['lat']) : -6.7924;
            $pLng = is_numeric($property['longitude'] ?? $property['lng'] ?? null) ? (float)($property['longitude'] ?? $property['lng']) : 39.2083;
            $this->set($this->mapPickVars($pLat, $pLng));
        }
        return $this->render('/Pages/host-lodge-form');
    }
}
