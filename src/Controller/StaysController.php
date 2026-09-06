<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FastnetApiClient;
use App\Service\StaysService;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use DateTimeImmutable;

/**
 * StaysController
 * Modular controller managing accommodations search, filtering, and property detail pages.
 * 100% dynamic - Zero hardcoded mock arrays.
 */
class StaysController extends AppController
{
    protected StaysService $staysService;
    protected FastnetApiClient $apiClient;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->staysService = new StaysService($this->apiClient);
    }

    /**
     * Stays search and listing (hotel-list-01)
     */
    public function index()
    {
        [$queryParams, $searchErrors] = $this->normalizeSearchParams(
            $this->getRequest()->getQueryParams()
        );

        // Only normalized search fields cross the application/API boundary.
        $searchPayload = $queryParams;
        if ($queryParams['destination'] !== '') {
            $apiDestination = preg_replace(
                ['/salaam/i', '/sallma/i'],
                ['salam', 'salam'],
                $queryParams['destination']
            );
            $searchPayload['q'] = $apiDestination ?: $queryParams['destination'];
        }
        unset($searchPayload['destination']);

        // Fetch properties dynamically from API
        $properties = $this->staysService->searchProperties($searchPayload);

        // Filter by amenities if specified
        if (!empty($queryParams['amenities'])) {
            $requiredAmenities = is_array($queryParams['amenities']) ? $queryParams['amenities'] : explode(',', (string)$queryParams['amenities']);
            $requiredAmenities = array_map('strtolower', array_map('trim', $requiredAmenities));

            $properties = array_values(array_filter($properties, function ($prop) use ($requiredAmenities) {
                $rawAm = $prop['amenities'] ?? [];
                if (is_string($rawAm)) {
                    $decoded = json_decode($rawAm, true);
                    $propAms = is_array($decoded) ? $decoded : explode(',', $rawAm);
                } elseif (is_array($rawAm)) {
                    $propAms = $rawAm;
                } else {
                    $propAms = [];
                }
                $propAmText = strtolower(implode(' ', $propAms) . ' ' . ($prop['description'] ?? ''));

                foreach ($requiredAmenities as $req) {
                    if (!empty($req) && !str_contains($propAmText, $req)) {
                        return false;
                    }
                }
                return true;
            }));
        }

        // Filter by price range
        if (!empty($queryParams['min_price'])) {
            $minP = (float)$queryParams['min_price'];
            $properties = array_values(array_filter($properties, fn($p) => ((float)($p['price_per_night'] ?? ($p['price'] ?? 0))) >= $minP));
        }
        if (!empty($queryParams['max_price'])) {
            $maxP = (float)$queryParams['max_price'];
            $properties = array_values(array_filter($properties, fn($p) => ((float)($p['price_per_night'] ?? ($p['price'] ?? 0))) <= $maxP));
        }

        // Filter by rating
        if (!empty($queryParams['rating'])) {
            $minR = (float)$queryParams['rating'];
            $properties = array_values(array_filter($properties, fn($p) => ((float)($p['reviews_avg_rating'] ?? ($p['rating'] ?? 8.5))) >= $minR));
        }

        $totalCount = count($properties);
        $this->set(compact('properties', 'queryParams', 'totalCount', 'searchErrors'));
        return $this->render('/pages/hotel-list-01');
    }

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

        $adults = $this->boundedSearchInt($input['adults'] ?? 2, 1, 16);
        $children = $this->boundedSearchInt($input['children'] ?? 0, 0, 8);
        $rooms = $this->boundedSearchInt($input['rooms'] ?? 1, 1, 8);
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

    /**
     * Hotel Detail & Rooms Listing
     */
    public function detail($id = null)
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $requestedPropertyId = $id !== null
            ? (int)$id
            : (int)($queryParams['id'] ?? 0);
        $propertyId = $requestedPropertyId;
        
        $property = null;
        if ($propertyId > 0) {
            $property = $this->staysService->getProperty($propertyId);
        }

        // Only use a featured property when the page was opened without an id.
        // A real property URL must never silently render a different hotel.
        if (!$property && $requestedPropertyId === 0) {
            $all = $this->staysService->getFeaturedResorts(1);
            if (!empty($all[0])) {
                $property = $all[0];
                $propertyId = (int)$property['id'];
            }
        }

        if (!$property) {
            throw new NotFoundException(__('Property not found.'));
        }

        // Fetch Rooms for Property dynamically
        $rooms = [];
        $roomsData = $this->apiClient->get('/properties/' . $propertyId . '/rooms', $queryParams);
        if (!empty($roomsData)) {
            $rooms = $roomsData['data'] ?? ($roomsData['items'] ?? $roomsData);
        }

        // If property object already has rooms loaded
        if (empty($rooms) && !empty($property['rooms']) && is_array($property['rooms'])) {
            $rooms = $property['rooms'];
        }

        $reviews = [];
        $reviewsData = $this->apiClient->get('/properties/' . $propertyId . '/reviews', [
            'page' => $queryParams['page'] ?? 1,
        ]);
        if (!empty($reviewsData)) {
            $reviews = $reviewsData['data'] ?? ($reviewsData['items'] ?? $reviewsData);
        }

        if (empty($queryParams['destination'])) {
            $queryParams['destination'] = $property['city'] ?? '';
        }

        $this->set(compact('property', 'rooms', 'reviews', 'queryParams', 'propertyId'));
        return $this->render('/pages/hotel-detail');
    }

    /**
     * Destinations listing
     */
    public function destination01()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $destinations = $this->staysService->getDestinationsSummary();
        $this->set(compact('destinations', 'queryParams'));
        return $this->render('/pages/destination-01');
    }
}
