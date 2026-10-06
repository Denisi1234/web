<?php
declare(strict_types=1);

namespace App\Controller\Host;

/**
 * HostOnboardingTrait — Multi-step property onboarding wizard.
 */
trait HostOnboardingTrait
{
    /**
     * Real multi-page onboarding wizard (?step=1..5) with session draft.
     * 1 Basics → 2 Location → 3 Photos → 4 Rooms (many, each with pictures)
     * → 5 Review & launch. Rooms belong to this lodge only.
     */
    public function onboarding()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $session = $this->getRequest()->getSession();

        $step = (int)$this->getRequest()->getQuery('step', 1);
        if ($step < 1 || $step > 5) $step = 1;
        $draft = $session->read('OnboardDraft');
        if (!is_array($draft)) $draft = [];

        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            $postedStep = (int)($data['wizard_step'] ?? $step);

            if ($postedStep === 1) {
                $draft['name'] = trim((string)($data['name'] ?? ''));
                $draft['type'] = trim((string)($data['type'] ?? 'Lodge'));
                $draft['description'] = trim((string)($data['description'] ?? ''));
                $draft['price_per_night'] = (float)($data['price_per_night'] ?? 0);
                if ($draft['name'] === '' || $draft['price_per_night'] <= 0) {
                    $this->Flash->error(__('Property name and nightly price are required.'));
                    $this->set(compact('userProfile', 'draft', 'step'));
                    return $this->render('/Pages/host-onboarding');
                }
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 2]]);
            }

            if ($postedStep === 2) {
                $draft['city'] = trim((string)($data['city'] ?? ''));
                $draft['area'] = trim((string)($data['area'] ?? ''));
                $draft['address'] = trim((string)($data['address'] ?? ''));
                $draft['latitude'] = is_numeric($data['latitude'] ?? null) ? (float)$data['latitude'] : -6.7924;
                $draft['longitude'] = is_numeric($data['longitude'] ?? null) ? (float)$data['longitude'] : 39.2083;
                if ($draft['city'] === '') {
                    $this->Flash->error(__('City is required — pick your location on the map.'));
                    $step = 2;
                    $this->set(compact('userProfile', 'draft', 'step'));
                    $this->set($this->mapPickVars((float)($draft['latitude'] ?? -6.7924), (float)($draft['longitude'] ?? 39.2083)));
                    return $this->render('/Pages/host-onboarding');
                }
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 3]]);
            }

            if ($postedStep === 3) {
                $draft['image_url'] = trim((string)($data['image_url'] ?? ''));
                if ($draft['image_url'] === '') {
                    $this->Flash->error(__('A cover photo is required — upload one above.'));
                    $step = 3;
                    $this->set(compact('userProfile', 'draft', 'step'));
                    return $this->render('/Pages/host-onboarding');
                }
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 4]]);
            }

            if ($postedStep === 4) {
                // Category-first rooms: each category (Deluxe…) carries shared
                // attributes + many room numbers (45, 78…). Expand to rows.
                $rooms = [];
                $rawCats = $data['cats'] ?? [];
                if (is_array($rawCats)) {
                    foreach ($rawCats as $cat) {
                        if (!is_array($cat)) continue;
                        $price = (float)($cat['price'] ?? 0);
                        if ($price <= 0) continue;
                        $nums = [];
                        foreach ((array)($cat['numbers'] ?? []) as $n) {
                            $n = trim((string)$n);
                            if ($n !== '' && !in_array($n, $nums, true)) $nums[] = $n;
                        }
                        if (empty($nums)) continue;
                        $photos = [];
                        foreach ((array)($cat['photos'] ?? []) as $ph) {
                            $ph = trim((string)$ph);
                            if ($ph !== '') $photos[] = $ph;
                        }
                        $photos = array_values($photos);
                        $shared = [
                            'room_type' => trim((string)($cat['room_type'] ?? 'Standard')),
                            'price' => $price,
                            'capacity' => max(1, (int)($cat['capacity'] ?? 2)),
                            'max_adults' => max(1, (int)($cat['max_adults'] ?? $cat['capacity'] ?? 2)),
                            'max_children' => max(0, (int)($cat['max_children'] ?? 0)),
                            'bed_configuration' => trim((string)($cat['bed_configuration'] ?? '')),
                            'status' => trim((string)($cat['status'] ?? 'available')),
                            'amenities' => $this->amenityList($cat['amenities'] ?? []),
                            'photos' => $photos,
                        ];
                        foreach ($nums as $num) {
                            $rooms[] = array_merge(['room_number' => $num], $shared);
                        }
                    }
                }
                $draft['rooms'] = $rooms;
                // Keep category view for re-render: stash raw groups too
                $draft['roomCats'] = is_array($rawCats) ? array_values($rawCats) : [];
                $session->write('OnboardDraft', $draft);
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 5]]);
            }

            // Step 5 · Review & launch: lodge + all its rooms, then verify
            $draft['amenities'] = $this->amenityList($data['amenities'] ?? ($draft['amenities'] ?? []));
            $payload = $this->buildPropertyPayload($draft);
            $payload['amenities'] = $draft['amenities'];
            if ($payload['name'] === '' || $payload['price_per_night'] <= 0) {
                $this->Flash->error(__('Basics are incomplete — back to step 1.'));
                return $this->redirect(['action' => 'onboarding', '?' => ['step' => 1]]);
            }
            $res = $this->apiClient->post('/properties', $payload, $headers);
            // Dead token → re-login with progress intact (draft stays in session)
            if ($bounce = $this->bounceOnUnauth($res, '/host/onboarding?step=5')) {
                $session->write('OnboardDraft', $draft);
                return $bounce;
            }
            if (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                $this->Flash->error(__($this->propertyErrorMessage($res)));
                $step = 5;
                $session->write('OnboardDraft', $draft);
                $this->set(compact('userProfile', 'draft', 'step'));
                return $this->render('/Pages/host-onboarding');
            }
            $pid = (int)($res['id'] ?? $res['data']['id'] ?? $res['property']['id'] ?? $res['data']['property']['id'] ?? 0);
            if ($pid <= 0) {
                // Backend said OK but returned no id — never claim success.
                try {
                    \Cake\Log\Log::error(sprintf('[Host onboarding] POST /properties ok but no id: %s', json_encode($res)));
                } catch (\Throwable $e) {
                }
                $this->Flash->error(__($this->propertyErrorMessage($res) ?: 'Could not create listing. Please try again.'));
                $step = 5;
                $session->write('OnboardDraft', $draft);
                $this->set(compact('userProfile', 'draft', 'step'));
                return $this->render('/Pages/host-onboarding');
            }
            $roomFails = [];
            $roomFailMsg = '';
            $roomUnauth = false;

            $multiRequests = [];
            foreach ((array)($draft['rooms'] ?? []) as $i => $rm) {
                if (is_array($rm)) {
                    $rm['property_id'] = $pid;
                    $rmPrice = (float)($rm['price'] ?? $rm['price_per_night'] ?? $rm['customer_price'] ?? 0);
                    $rm['price'] = $rmPrice;
                    $rm['price_per_night'] = $rmPrice;
                    $rm['customer_price'] = $rmPrice;
                }
                $multiRequests[$i] = ['endpoint' => '/properties/' . $pid . '/rooms', 'data' => $rm];
            }
            if (!empty($multiRequests)) {
                $multiResults = $this->apiClient->postMulti($multiRequests, $headers);
                foreach ($multiResults as $i => $rRes) {
                    if (is_array($rRes) && (int)($rRes['_status'] ?? 0) === 401) { $roomUnauth = true; }
                    if (empty($rRes) || (!empty($rRes['_status']) && (int)$rRes['_status'] >= 400)) {
                        // Extract original room number to report failure
                        $rm = $multiRequests[$i]['data'];
                        $roomFails[] = (string)($rm['room_number'] ?? '?');
                        if ($roomFailMsg === '') {
                            $roomFailMsg = is_array($rRes) ? (string)($rRes['message'] ?? '') : 'room service unavailable';
                            try {
                                \Cake\Log\Log::error(sprintf('[Host onboarding] POST /properties/%d/rooms failed for room %s: %s', $pid, (string)($rm['room_number'] ?? '?'), json_encode($rRes)));
                            } catch (\Throwable $e) {
                            }
                        }
                    }
                }
            }

            if ($roomUnauth) {
                // Lodge exists — rooms can be added after re-login; keep no stale draft
                $session->delete('OnboardDraft');
                $this->Flash->error(__('Session expired — please sign in again to add rooms.'));
                return $this->redirect('/login?redirect=' . urlencode('/host/rooms/add?property_id=' . $pid));
            }
            $this->submitLodgeVerification($pid, $headers);
            $this->clearHostPropertiesCache();
            $session->delete('OnboardDraft');
            $nRooms = count((array)($draft['rooms'] ?? [])) - count($roomFails);
            if (!empty($roomFails)) {
                $detail = $roomFailMsg !== '' ? ' (' . mb_substr($roomFailMsg, 0, 120) . ')' : ' — numbers may already exist';
                $this->Flash->error(__('Rooms not created ({0}){1}. Add them under Rooms.', implode(', ', $roomFails), $detail));
            }
            if ($nRooms > 0) {
                $this->Flash->success(__('Property onboarded with {0} room(s) and submitted for verification.', $nRooms));
            } else {
                $this->Flash->success(__('Property created — add rooms under Rooms. It was submitted for verification.'));
            }
            return $this->redirect(['action' => 'rooms']);
        }

        $this->set(compact('userProfile', 'draft', 'step'));
        if ($step === 2) {
            $this->set($this->mapPickVars((float)($draft['latitude'] ?? -6.7924), (float)($draft['longitude'] ?? 39.2083)));
        }
        return $this->render('/Pages/host-onboarding');
    }
}
