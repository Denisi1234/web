<?php
declare(strict_types=1);

namespace App\Controller\Host;

/**
 * HostPropertyFormTrait — Property payload builders, wizard room collection, and map vars.
 */
trait HostPropertyFormTrait
{
    /**
     * Map-picker vars for location steps: token/style resolved server-side
     * (2s fail-fast, cached 5min) + an instant static preview image, so the
     * step-2 map paints immediately without waiting on extra round trips.
     */
    private function mapPickVars(float $lat, float $lng): array
    {
        $mapToken = '';
        $mapStyle = 'mapbox://styles/mapbox/streets-v12';
        $mapPreviewUrl = '';
        try {
            $maps = new \App\Service\MapService($this->apiClient);
            $cfg = $maps->getMapConfig(2);
            if (!empty($cfg['token']) && is_string($cfg['token']) && str_starts_with($cfg['token'], 'pk.')) {
                $mapToken = $cfg['token'];
            }
            if (!empty($cfg['style']) && is_string($cfg['style'])) {
                $mapStyle = $cfg['style'];
            }
            $mapPreviewUrl = $maps->getStaticMapUrl($lat, $lng, 12, 640, 340);
        } catch (\Throwable $e) {
        }
        return compact('mapToken', 'mapStyle', 'mapPreviewUrl');
    }

    /**
     * Build property payload with backend-required defaults (area defaults to city).
     */
    private function buildPropertyPayload(array $data): array
    {
        $city = trim((string)($data['city'] ?? 'Dar es Salaam'));
        if ($city === '') $city = 'Dar es Salaam';
        $area = trim((string)($data['area'] ?? ''));
        if ($area === '') $area = $city;
        return [
            'name' => trim((string)($data['name'] ?? '')),
            'description' => trim((string)($data['description'] ?? '')),
            'address' => trim((string)($data['address'] ?? '')),
            'city' => $city,
            'area' => $area,
            // Step-1 type (Lodge/Hotel/Apartment) was previously dropped here,
            // so every property saved without a type. Map to the backend enum.
            'property_type' => self::mapPropertyType($data['type'] ?? $data['property_type'] ?? ''),
            'price_per_night' => (float)($data['price_per_night'] ?? 0),
            'latitude' => is_numeric($data['latitude'] ?? null) ? (float)$data['latitude'] : -6.7924,
            'longitude' => is_numeric($data['longitude'] ?? null) ? (float)$data['longitude'] : 39.2083,
            'image_url' => trim((string)($data['image_url'] ?? '')),
            // The API accepts an amenities array; amenityList() also accepts a
            // comma-separated string, so the free-text field works directly.
            'amenities' => $this->amenityList($data['property_amenities'] ?? []),
        ];
    }

    /**
     * Map the onboarding step-1 type to the backend property_type enum
     * (Hotel, Resort, Apartment, Safari Lodge, Villa).
     */
    public static function mapPropertyType(mixed $raw): string
    {
        $t = strtolower(trim((string)$raw));
        return match (true) {
            $t === 'hotel' => 'Hotel',
            $t === 'resort' => 'Resort',
            $t === 'apartment' => 'Apartment',
            $t === 'villa' => 'Villa',
            $t === 'safari lodge', $t === 'lodge', $t === 'safari' => 'Safari Lodge',
            default => 'Safari Lodge',
        };
    }

    private function propertyErrorMessage(?array $res): string
    {
        if (empty($res)) return 'Service unavailable. Please try again.';
        $msg = trim((string)($res['message'] ?? 'Could not create listing.'));
        if (!empty($res['errors']) && is_array($res['errors'])) {
            $flat = [];
            foreach ($res['errors'] as $fieldErrors) {
                foreach ((array)$fieldErrors as $e) $flat[] = $e;
            }
            if (!empty($flat)) $msg .= ' ' . implode(' ', array_slice($flat, 0, 3));
        }
        return $msg;
    }

    private function submitLodgeVerification(int $propertyId, array $headers): void
    {
        try {
            $this->apiClient->post('/verification/lodge/' . $propertyId, [], $headers);
        } catch (\Throwable $e) {
            // Non-fatal: property is created Active by default; admin can still review
        }
    }

    public function create()
    {
        return $this->redirect(['action' => 'onboarding'], 301);
    }

    /**
     * Pull the repeatable room rows out of the wizard submission.
     *
     * Rows are named rooms[0][room_number] etc. Completely blank rows are
     * dropped so the host is not forced to fill every row they added.
     *
     * @return array<int, array<string, mixed>>
     */
    private function collectWizardRooms(array $data): array
    {
        $rows = $data['rooms'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $roomNumber = trim((string)($row['room_number'] ?? ''));
            $price = (float)($row['price'] ?? 0);

            // A row with neither an identifier nor a rate is an unused slot.
            if ($roomNumber === '' && $price <= 0.0) {
                continue;
            }

            $out[] = [
                'room_number'        => $roomNumber,
                'room_type'          => trim((string)($row['room_type'] ?? '')) ?: 'Standard',
                'price'              => $price,
                'capacity'           => max(1, (int)($row['capacity'] ?? 2)),
                'max_adults'         => max(1, (int)($row['max_adults'] ?? 0)) ?: null,
                'max_children'       => max(0, (int)($row['max_children'] ?? 0)),
                'number_of_beds'     => max(1, (int)($row['number_of_beds'] ?? 1)),
                'bed_configuration'  => trim((string)($row['bed_configuration'] ?? '')),
                'room_size'          => trim((string)($row['room_size'] ?? '')),
                'floor'              => trim((string)($row['floor'] ?? '')),
                'status'             => trim((string)($row['status'] ?? 'available')) ?: 'available',
                'description'        => trim((string)($row['description'] ?? '')),
                'amenities'          => $this->amenityList($row['amenities'] ?? []),
            ];
        }

        return $out;
    }

    /**
     * Create rooms one at a time so a single bad row cannot lose the others.
     *
     * @param array<int, array<string, mixed>> $rooms
     * @return array{0:int,1:array<int,string>,2:bool} [createdCount, errors, authFailed]
     */
    private function createRooms(int $propertyId, array $rooms, array $headers): array
    {
        $created = 0;
        $errors = [];
        $authFailed = false;

        foreach ($rooms as $index => $room) {
            if ($room['room_number'] === '' || $room['price'] <= 0.0) {
                $label = $room['room_number'] !== '' ? $room['room_number'] : ('Room ' . ($index + 1));
                $errors[$index] = $label . ': room number and price are both required.';
                continue;
            }

            $rPrice = (float)($room['price'] ?? $room['price_per_night'] ?? $room['customer_price'] ?? 0);
            $payload = array_filter([
                'property_id'       => $propertyId,
                'room_number'       => $room['room_number'] ?? '',
                'room_type'         => $room['room_type'] ?? 'Standard',
                'price'             => $rPrice,
                'price_per_night'   => $rPrice,
                'customer_price'    => $rPrice,
                'capacity'          => (int)($room['capacity'] ?? 1),
                'max_adults'        => (int)($room['max_adults'] ?? $room['capacity'] ?? 1),
                'max_children'      => (int)($room['max_children'] ?? 0),
                'number_of_beds'    => (int)($room['number_of_beds'] ?? 1),
                'bed_configuration' => $room['bed_configuration'] ?? '',
                'room_size'         => $room['room_size'] ?? '',
                'floor'             => $room['floor'] ?? '',
                'status'            => $room['status'] ?? 'available',
                'description'       => $room['description'] ?? '',
                'amenities'         => $room['amenities'] ?? [],
            ], static fn($v) => $v !== null && $v !== '');

            $res = $this->apiClient->post('/properties/' . $propertyId . '/rooms', $payload, $headers);
            if (empty($res) || (!empty($res['_status']) && (int)$res['_status'] >= 400 && (int)$res['_status'] !== 401)) {
                $altRes = $this->apiClient->post('/rooms', $payload, $headers);
                if (!empty($altRes) && (empty($altRes['_status']) || (int)$altRes['_status'] < 400)) {
                    $res = $altRes;
                }
            }

            // A dead session would otherwise surface as N identical per-room
            // errors. Flag it so the caller can bounce to sign-in instead.
            $status = (int)($res['_status'] ?? 0);
            if ($res === null || $status === 401) {
                $authFailed = true;
                return [$created, $errors, true];
            }

            if ($status >= 400) {
                $errors[$index] = $room['room_number'] . ': ' . $this->propertyErrorMessage($res);
                continue;
            }

            $created++;
        }

        return [$created, $errors, false];
    }
}
