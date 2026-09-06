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

        $propertyResponse = $this->apiClient->get('/properties/' . $propertyId);
        $property = $propertyResponse['data'] ?? $propertyResponse;
        if (!is_array($property) || empty($property)) {
            throw new InvalidArgumentException('This stay is no longer available.');
        }

        $roomsResponse = $this->apiClient->get('/properties/' . $propertyId . '/rooms');
        $availableRooms = $roomsResponse['data'] ?? ($roomsResponse['items'] ?? $roomsResponse);
        if (!is_array($availableRooms)) {
            $availableRooms = [];
        }
        if (empty($availableRooms) && !empty($property['rooms']) && is_array($property['rooms'])) {
            $availableRooms = $property['rooms'];
        }

        $room = null;
        foreach ($availableRooms as $candidate) {
            if (is_array($candidate) && (int)($candidate['id'] ?? 0) === $roomId) {
                $room = $candidate;
                break;
            }
        }
        if (!$room) {
            throw new InvalidArgumentException('This room is no longer available.');
        }

        $maxAdults = (int)($room['max_adults'] ?? 0);
        $maxChildren = (int)($room['max_children'] ?? 0);
        if (($maxAdults > 0 && $adults > $maxAdults) || ($maxChildren >= 0 && $children > $maxChildren)) {
            throw new InvalidArgumentException('This room cannot accommodate the selected guests.');
        }

        $price = $this->roomPrice($room);
        if ($price <= 0) {
            throw new InvalidArgumentException('A current price is not available for this room.');
        }

        $nights = (int)$checkOut->diff($checkIn)->days;
        $subtotal = $price * $nights * $rooms;
        $taxes = round($subtotal * 0.18);
        $total = $subtotal + $taxes;

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
            'property' => $property,
            'room' => $room,
            'calculation' => [
                'price_per_night' => $price,
                'subtotal' => $subtotal,
                'taxes' => $taxes,
                'total_amount' => $total,
                'nights' => $nights,
                'rooms_count' => $rooms,
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
