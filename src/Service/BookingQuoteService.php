<?php
declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Creates short-lived, server-owned quotes for a selected room and stay.
 */
class BookingQuoteService
{
    public function __construct(private readonly FastnetApiClient $apiClient)
    {
    }

    public function create(int $propertyId, int $roomId, array $input): array
    {
        if ($propertyId < 1 || $roomId < 1) {
            throw new InvalidArgumentException('Choose a valid stay and room.');
        }

        $checkIn = $this->parseDate($input['checkIn'] ?? $input['check_in'] ?? null);
        $checkOut = $this->parseDate($input['checkOut'] ?? $input['check_out'] ?? null);
        $today = new DateTimeImmutable('today');
        if (!$checkIn || $checkIn < $today) {
            throw new InvalidArgumentException('Choose a valid check-in date.');
        }
        if (!$checkOut || $checkOut <= $checkIn) {
            throw new InvalidArgumentException('Check-out must be after check-in.');
        }

        $adults = $this->boundedInt($input['adults'] ?? 2, 1, 16);
        $children = $this->boundedInt($input['children'] ?? 0, 0, 8);
        $rooms = $this->boundedInt($input['rooms'] ?? 1, 1, 8);
        if ($adults + $children > 32) {
            throw new InvalidArgumentException('The guest count cannot exceed 32 people.');
        }
        $guests = $adults + $children;

        // Authoritative backend calculation — POST /api/bookings/calculate
        $payload = [
            'property_id' => $propertyId,
            'room_id' => $roomId,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'guests' => $guests,
            'rooms_count' => $rooms,
            'quantity' => $rooms,
            'rooms' => [['room_id' => $roomId, 'quantity' => $rooms]],
        ];
        $isLocal = \Cake\Core\Configure::read('debug')
            || in_array(env('HTTP_HOST', ''), ['localhost', '127.0.0.1', 'localhost:8080', 'localhost:8765']) 
            || in_array(env('SERVER_NAME', ''), ['localhost', '127.0.0.1'])
            || str_contains(env('HTTP_HOST', ''), 'localhost')
            || str_contains(env('HTTP_HOST', ''), '127.0.0.1');

        $calc = $this->apiClient->post('/bookings/calculate', $payload);
        // Only fallback to GET if POST returned null (no response)
        if (empty($calc)) {
            $calc = $this->apiClient->get('/bookings/calculate', $payload);
        }

        // If backend returned invalid/error or unavailable, handle local demo fallback or throw
        $isCalcError = empty($calc) 
            || (isset($calc['valid']) && $calc['valid'] === false) 
            || (isset($calc['_status']) && $calc['_status'] >= 400);

        if ($isCalcError) {
            if ($isLocal) {
                $calcNights = max(1, (int)$checkOut->diff($checkIn)->days);
                $nightlyRate = !empty($input['price']) ? (float)$input['price'] : 150000;
                $subtotal = $nightlyRate * $calcNights * $rooms;
                $propTitle = ($propertyId >= 9000 || $propertyId === 1) ? 'Serena View Lodge' : 'FastNet Resort & Spa';
                $roomName = ($roomId === 9002) ? 'Executive Suite' : (($roomId === 9003) ? 'Standard Room' : 'Deluxe Ocean View Room');
                $calc = [
                    'valid' => true,
                    'nights' => $calcNights,
                    'pricing' => [
                        'owner_base_subtotal' => $subtotal,
                        'subtotal' => $subtotal,
                        'taxes' => 0,
                        'azampay_fee' => 0,
                        'total' => $subtotal,
                        'grand_total' => $subtotal,
                    ],
                    'property' => [
                        'id' => $propertyId,
                        'name' => $propTitle,
                        'city' => 'Dar es Salaam',
                        'area' => 'Masaki',
                        'address' => '123 Ocean Road, Masaki, Dar es Salaam',
                        'star_rating' => 4,
                        'rating' => 8.9,
                        'reviews_count' => 124,
                        'image_url' => '/assets/img/hotel/hotel-1.jpg',
                        'amenities' => ['Free WiFi', 'Swimming Pool', 'Air Conditioning', 'Breakfast included', 'Ocean View', 'Free Parking'],
                    ],
                    'rooms' => [
                        [
                            'room_id' => $roomId,
                            'id' => $roomId,
                            'name' => $roomName,
                            'nightly_rate' => $nightlyRate,
                            'price' => $nightlyRate,
                            'customer_price' => $nightlyRate,
                            'max_adults' => 2,
                            'max_children' => 1,
                            'bed_configuration' => ($roomId === 9002) ? '2 Queen Beds' : '1 King Bed',
                            'size' => ($roomId === 9002) ? 58 : 38,
                            'photos' => ['/assets/img/hotel/hotel-1.jpg'],
                            'amenities' => ['King Bed', 'Balcony', 'En-suite Bathroom', 'Smart TV', 'Mini Bar'],
                        ]
                    ],
                    'cancellation_policy' => 'Free cancellation before ' . date('M j, Y', strtotime($checkIn->format('Y-m-d') . ' -1 day')),
                ];
            } else {
                if (isset($calc['valid']) && $calc['valid'] === false) {
                    throw new InvalidArgumentException($calc['message'] ?? 'Room not available for selected dates.');
                }
                if (isset($calc['_status']) && $calc['_status'] >= 400) {
                    throw new InvalidArgumentException($calc['message'] ?? 'Unable to calculate price — please check dates and try again.');
                }
                throw new InvalidArgumentException('Unable to calculate room price — backend unavailable. Please try again.');
            }
        }

        // Extract authoritative pricing
        $property = $calc['property'] ?? null;
        $fetchedFull = false;
        if (empty($property)) {
            // Fallback fetch property/room for display if backend didn't return
            $propertyResponse = $this->apiClient->get('/properties/' . $propertyId);
            if (!empty($propertyResponse) && empty($propertyResponse['_status'])) {
                $property = $propertyResponse['data'] ?? $propertyResponse;
                $fetchedFull = true;
            }
        }
        // Enrich display fields (amenities, photos, bed, ratings) — /bookings/calculate
        // returns pricing-only snapshots, but the quote page renders real content.
        // Calc values win on conflict; full fetch only fills gaps.
        if (!$fetchedFull && !empty($property['id'])) {
            try {
                $fullResponse = $this->apiClient->get('/properties/' . $propertyId);
                $fullProperty = (is_array($fullResponse) && empty($fullResponse['_status'])) ? ($fullResponse['data'] ?? $fullResponse) : null;
                if (is_array($fullProperty) && isset($fullProperty['id'])) {
                    $property = is_array($property) ? $property + $fullProperty : $fullProperty;
                }
            } catch (\Throwable $e) {
                // display enrichment is best-effort; pricing already authoritative
            }
        }
        if (empty($property) && $isLocal) {
            $property = [
                'id' => $propertyId,
                'name' => 'Serena View Lodge',
                'city' => 'Dar es Salaam',
                'area' => 'Masaki',
                'address' => '123 Ocean Road, Masaki',
                'star_rating' => 4,
                'rating' => 8.9,
                'reviews_count' => 124,
                'image_url' => '/assets/img/hotel/hotel-1.jpg',
            ];
        }

        $room = null;
        if (!empty($calc['rooms'][0])) {
            $room = $calc['rooms'][0];
            // map backend room fields to frontend expected
            $room['id'] = $room['room_id'] ?? $roomId;
            $room['price'] = $room['nightly_rate'] ?? $room['owner_nightly_rate'] ?? 0;
            $room['customer_price'] = $room['nightly_rate'] ?? 0;
        }
        if (!$room) {
            // fallback fetch room
            $roomsResponse = $this->apiClient->get('/properties/' . $propertyId . '/rooms');
            $availableRooms = (!empty($roomsResponse) && empty($roomsResponse['_status'])) ? ($roomsResponse['data'] ?? ($roomsResponse['items'] ?? $roomsResponse ?? [])) : [];
            if (!empty($property['rooms']) && empty($availableRooms)) $availableRooms = $property['rooms'];
            foreach ((array)$availableRooms as $candidate) {
                if ((int)($candidate['id'] ?? 0) === $roomId) { $room = $candidate; break; }
            }
        }
        // Enrich room display fields from the full property payload (calc rooms carry pricing only).
        // Calc pricing keys win; everything else fills from the matching full room.
        if (is_array($room) && !empty($property['rooms']) && is_array($property['rooms'])) {
            foreach ($property['rooms'] as $full) {
                if (is_array($full) && (int)($full['id'] ?? 0) === (int)($room['id'] ?? $roomId)) {
                    $room = $room + $full;
                    break;
                }
            }
        }
        if (!$room && $isLocal) {
            $nightlyRate = !empty($input['price']) ? (float)$input['price'] : 150000;
            $room = [
                'id' => $roomId,
                'room_id' => $roomId,
                'name' => 'Deluxe Room',
                'price' => $nightlyRate,
                'customer_price' => $nightlyRate,
                'nightly_rate' => $nightlyRate,
                'bed_configuration' => '1 King Bed',
                'max_adults' => 2,
                'max_children' => 1,
                'size' => 32,
                'photos' => ['/assets/img/hotel/hotel-1.jpg'],
            ];
        }
        if (!$room) {
            throw new InvalidArgumentException('This room is no longer available.');
        }

        $pricing = $calc['pricing'] ?? [];
        $pricePerNight = (float)($room['nightly_rate'] ?? $room['price'] ?? $pricing['owner_base_subtotal'] ?? 0);
        // If nightly_rate is customer rate, use that; otherwise derive
        if (!empty($calc['rooms'][0]['nightly_rate'])) {
            $pricePerNight = (float)$calc['rooms'][0]['nightly_rate'];
        } elseif (!empty($pricing['subtotal'])) {
            $nightsTmp = (int)($calc['nights'] ?? $checkOut->diff($checkIn)->days);
            $pricePerNight = $nightsTmp > 0 ? (float)$pricing['subtotal'] / $nightsTmp / $rooms : $pricePerNight;
        }

        $nights = (int)($calc['nights'] ?? $checkOut->diff($checkIn)->days);
        $subtotal = (float)($pricing['subtotal'] ?? $pricing['owner_base_subtotal'] ?? ($pricePerNight * $nights * $rooms));
        // Backend uses 0% VAT + 1% AzamPay fee — map to frontend taxes/fees
        $azampayFee = (float)($pricing['azampay_fee'] ?? 0);
        $taxes = (float)($pricing['taxes'] ?? 0);
        // Frontend expects 18% VAT-like taxes — combine backend taxes + fee for display compatibility, but keep authoritative total
        $total = (float)($pricing['total'] ?? $pricing['grand_total'] ?? $calc['grand_total'] ?? ($subtotal + $azampayFee + $taxes));

        // Security lock timestamp — 15-minute hold token quote_id
        return [
            'quote_id' => bin2hex(random_bytes(16)),
            'created_at' => time(),
            'expires_at' => time() + 900,
            'property_id' => $propertyId,
            'room_id' => $roomId,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'adults' => $adults,
            'children' => $children,
            'rooms' => $rooms,
            'property' => is_array($property) ? $property : [],
            'room' => $room,
            'calculation' => [
                'price_per_night' => $pricePerNight,
                'subtotal' => $subtotal,
                'taxes' => $taxes + $azampayFee,
                'azampay_fee' => $azampayFee,
                'total_amount' => $total,
                'nights' => $nights,
                'rooms_count' => $rooms,
                'cancellation_policy' => $calc['cancellation_policy'] ?? "Free cancellation before " . $checkIn->format('Y-m-d'),
                'raw_pricing' => $pricing,
                'raw' => $calc,
            ],
        ];
    }

    private function roomPrice(array $room): float
    {
        foreach (['customer_price', 'customer_price_per_night', 'price_per_night', 'price'] as $key) {
            if (isset($room[$key]) && is_numeric($room[$key])) {
                return (float)$room[$key];
            }
        }
        return 0.0;
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }
        return $date;
    }

    private function boundedInt(mixed $value, int $default, int $max): int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT);
        if ($number === false) {
            return $default;
        }
        return max($default === 0 ? 0 : 1, min($max, (int)$number));
    }
}
