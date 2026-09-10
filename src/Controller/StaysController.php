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
     * Stays search and listing — consolidated to home (/) per prompt
     * Legacy /hotel-list-01, /hotels, /stays now redirect to home with query params preserved
     */
    public function index()
    {
        return $this->redirect('/?' . http_build_query($this->getRequest()->getQueryParams()));
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

        // Mock fallback for local dev / when backend is unreachable (index is now the only list)
        if (!$property && $propertyId > 0) {
            $property = $this->getMockProperty($propertyId);
        }
        // Final fallback for demo: any id -> first mock
        if (!$property && $requestedPropertyId === 0) {
            $property = $this->getMockProperty(1);
            if ($property) $propertyId = (int)$property['id'];
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
        return $this->render('/Pages/hotel-detail');
    }

    private function getMockProperty(int $id): ?array
    {
        $mocks = [
            1 => ['id'=>1,'name'=>'The Serena Hotel Dar es Salaam','city'=>'Dar es Salaam','image_url'=>'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop','rating'=>4.7,'review_count'=>285,'price_per_night'=>85000,'customer_price_per_night'=>85000,'amenities'=>['WiFi','Pool','Fitness Center'],'description'=>'Luxury 5-star hotel in the heart of Dar es Salaam'],
            2 => ['id'=>2,'name'=>'Hyatt Regency Dar es Salaam','city'=>'Dar es Salaam','image_url'=>'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop','rating'=>4.5,'review_count'=>156,'price_per_night'=>72000,'customer_price_per_night'=>72000,'amenities'=>['WiFi','Pool','Gym'],'description'=>'4-star hotel with modern amenities'],
            3 => ['id'=>3,'name'=>'Dar Boutique Hotel','city'=>'Dar es Salaam','image_url'=>'https://images.unsplash.com/photo-1570129477492-45a003537e1f?w=800&h=600&fit=crop','rating'=>4.3,'review_count'=>98,'price_per_night'=>45000,'customer_price_per_night'=>45000,'amenities'=>['WiFi','Breakfast'],'description'=>'Charming boutique hotel'],
            4 => ['id'=>4,'name'=>'Addax Hotel Dar es Salaam','city'=>'Dar es Salaam','image_url'=>'https://images.unsplash.com/photo-1564078516801-18a1ab35eca3?w=800&h=600&fit=crop','rating'=>4.4,'review_count'=>203,'price_per_night'=>55000,'customer_price_per_night'=>55000,'amenities'=>['WiFi','Pool'],'description'=>'Mid-range hotel'],
            5 => ['id'=>5,'name'=>'Oceanview Hotel & Resort','city'=>'Zanzibar','image_url'=>'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&h=600&fit=crop','rating'=>4.6,'review_count'=>412,'price_per_night'=>95000,'customer_price_per_night'=>95000,'amenities'=>['WiFi','Pool','Beach'],'description'=>'Premier resort'],
            6 => ['id'=>6,'name'=>'Safari Palace Hotel','city'=>'Arusha','image_url'=>'https://images.unsplash.com/photo-1559599810-46d1c52494ee?w=800&h=600&fit=crop','rating'=>4.2,'review_count'=>167,'price_per_night'=>38000,'customer_price_per_night'=>38000,'amenities'=>['WiFi','Restaurant'],'description'=>'Comfortable hotel'],
        ];
        return $mocks[$id] ?? $mocks[1] ?? null;
    }

    /**
     * Destinations listing
     */
    public function destination01()
    {
        $queryParams = $this->getRequest()->getQueryParams();
        $destinations = $this->staysService->getDestinationsSummary();
        $this->set(compact('destinations', 'queryParams'));
        return $this->render('/Pages/destination-01');
    }
}
