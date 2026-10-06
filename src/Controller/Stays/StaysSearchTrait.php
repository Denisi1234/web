<?php
declare(strict_types=1);

namespace App\Controller\Stays;

use DateTimeImmutable;

/**
 * StaysSearchTrait — Search param normalization helpers.
 */
trait StaysSearchTrait
{
    /**
     * Normalize the public search contract and keep invalid values visible to the user.
     * Search parameters are intentionally bounded before they reach the API or templates.
     */
    private function normalizeSearchParams(array $input): array
    {
        $today = new DateTimeImmutable('today');
        $errors = [];

        $destination = trim((string)($input['destination'] ?? ($input['q'] ?? '')));
        if (mb_strlen($destination) > 120) {
            $destination = mb_substr($destination, 0, 120);
            $errors[] = 'The destination was shortened to 120 characters.';
        }

        $checkIn = $this->parseSearchDate($input['checkIn'] ?? null);
        if (!$checkIn) {
            $checkIn = $today->modify('+7 days');
            if (!empty($input['checkIn'])) {
                $errors[] = 'Please choose a valid check-in date.';
            }
        }
        if ($checkIn < $today) {
            $checkIn = $today;
            $errors[] = 'Check-in cannot be before today.';
        }

        $checkOut = $this->parseSearchDate($input['checkOut'] ?? null);
        if (!$checkOut || $checkOut <= $checkIn) {
            $checkOut = $checkIn->modify('+1 day');
            if (!empty($input['checkOut'])) {
                $errors[] = 'Check-out must be after check-in.';
            }
        }

        $adults = $this->boundedSearchInt($input['adults'] ?? 2, 1, 10);
        $children = $this->boundedSearchInt($input['children'] ?? 0, 0, 6);
        $rooms = $this->boundedSearchInt($input['rooms'] ?? 1, 1, 5);
        if ($adults + $children > 32) {
            $children = max(0, 32 - $adults);
            $errors[] = 'The guest count cannot exceed 32 people.';
        }

        $params = [
            'destination' => $destination,
            'checkIn' => $checkIn->format('Y-m-d'),
            'checkOut' => $checkOut->format('Y-m-d'),
            'adults' => $adults,
            'children' => $children,
            'rooms' => $rooms,
        ];

        foreach (['lat', 'lng', 'min_price', 'max_price', 'rating', 'free_cancellation', 'pets', 'amenities'] as $key) {
            if (array_key_exists($key, $input) && $input[$key] !== '' && $input[$key] !== null) {
                $params[$key] = is_array($input[$key])
                    ? array_values(array_filter(array_map(
                        static fn(mixed $item): string => trim((string)$item),
                        $input[$key]
                    )))
                    : trim((string)$input[$key]);
            }
        }

        return [$params, $errors];
    }

    private function parseSearchDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $dateErrors = DateTimeImmutable::getLastErrors();
        if (!$date || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
            return null;
        }
        return $date;
    }

    private function boundedSearchInt(mixed $value, int $default, int $max): int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT);
        if ($number === false) {
            return $default;
        }
        return max($default === 0 ? 0 : 1, min($max, (int)$number));
    }
}
