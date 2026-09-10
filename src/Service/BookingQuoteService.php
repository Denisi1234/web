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
        $calc = $this->apiClient->post('/bookings/calculate', $payload);
        // Fallback to GET if POST not routed (backend supports both)
        if (empty($calc) || empty($calc['valid'])) {
            $calc = $this->apiClient->get('/bookings/calculate', $payload);
        }
        if (empty($calc)) {
            throw new InvalidArgumentException('Unable to calculate room price — backend unavailable. Please try again.');
        }
        if (isset($calc['valid']) && $calc['valid'] === false) {
            throw new InvalidArgumentException($calc['message'] ?? 'Room not available for selected dates.');
        }
        // Backend returns 422 with valid false on unavailability — surface as quote error
        if (isset($calc['message']) && !isset($calc['pricing']) && !isset($calc['grand_total'])) {
            // Might be error payload
            if (isset($calc['valid']) && $calc['valid'] === false) {
                throw new InvalidArgumentException($calc['message']);
            }
        }

        // Extract authoritative pricing
        $property = $calc['property'] ?? null;
        if (empty($property)) {
            // Fallback fetch property/room for display if backend didn't return
            $propertyResponse = $this->apiClient->get('/properties/' . $propertyId);
            $property = $propertyResponse['data'] ?? $propertyResponse;
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
            $availableRooms = $roomsResponse['data'] ?? ($roomsResponse['items'] ?? $roomsResponse ?? []);
            if (!empty($property['rooms']) && empty($availableRooms)) $availableRooms = $property['rooms'];
            foreach ((array)$availableRooms as $candidate) {
                if ((int)($candidate['id'] ?? 0) === $roomId) { $room = $candidate; break; }
            }
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
