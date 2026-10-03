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
            // Forward search context — backend attaches per-room availability for these dates/guests
            $adultsCtx = max(1, (int)($queryParams['adults'] ?? 2));
            $childrenCtx = max(0, (int)($queryParams['children'] ?? 0));
            $property = $this->staysService->getProperty($propertyId, [
                'check_in' => $queryParams['checkIn'] ?? $queryParams['check_in'] ?? $queryParams['checkin'] ?? null,
                'check_out' => $queryParams['checkOut'] ?? $queryParams['check_out'] ?? $queryParams['checkout'] ?? null,
                'guests' => $adultsCtx + $childrenCtx,
                'rooms' => max(1, (int)($queryParams['rooms'] ?? 1)),
            ]);
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

        // Backend is source of truth — unknown id must 404, never render
        // a different hotel or injected mock data.
        if (!$property) {
            throw new NotFoundException(__('Property not found.'));
        }

        // Fetch Rooms for Property dynamically — cached 120s in StaysService (was blocking HTTP every view)
        $rooms = $this->staysService->getRooms($propertyId, $queryParams);

        // If property object already has rooms loaded
        if (empty($rooms) && !empty($property['rooms']) && is_array($property['rooms'])) {
            $rooms = $property['rooms'];
        }
        // Drop backend error shapes ({message,_status}) and non-room scalars — templates index into room arrays
        if (is_array($rooms)) {
            $rooms = array_values(array_filter($rooms, fn($r) => is_array($r)));
        } else {
            $rooms = [];
        }
        // Dedupe rooms sharing a room_number (e.g. "ROOM 4005" twice) — keep the cheapest,
        // so detail never renders the same room card twice. Keyed case-insensitively; rooms
        // without any number are always kept.
        $seenNumbers = [];
        $deduped = [];
        foreach ($rooms as $r) {
            $numKey = strtolower(trim((string)($r['room_number'] ?? '')));
            if ($numKey === '') {
                $deduped[] = $r;
                continue;
            }
            if (!array_key_exists($numKey, $seenNumbers)) {
                $seenNumbers[$numKey] = count($deduped);
                $deduped[] = $r;
                continue;
            }
            $prevIdx = $seenNumbers[$numKey];
            $prevPrice = (float)($deduped[$prevIdx]['customer_price'] ?? ($deduped[$prevIdx]['price'] ?? INF));
            $curPrice = (float)($r['customer_price'] ?? ($r['price'] ?? INF));
            if ($curPrice < $prevPrice) {
                $deduped[$prevIdx] = $r;
            }
        }
        $rooms = $deduped;

        // Group rooms by CATEGORY (room_type_id: Standard, Suite, Deluxe…).
        // Physical rooms (numbers 34, 78, 90) live inside their category group —
        // detail renders one card per category, never one card per bed.
        $roomGroups = [];
        $groupOrder = [];
        foreach ($rooms as $r) {
            $rawType = trim((string)($r['room_type_id'] ?? ($r['type'] ?? '')));
            $typeKey = strtolower((string)preg_replace('/[^a-z0-9]/', '', $rawType));
            if ($typeKey === '') {
                $typeKey = '__standard__';
                $rawType = 'Standard';
            }
            if (!array_key_exists($typeKey, $roomGroups)) {
                $roomGroups[$typeKey] = [
                    'type' => $rawType,
                    'label' => \App\Utility\TextFormatter::formatTitle($rawType),
                    'rooms' => [],
                ];
                $groupOrder[] = $typeKey;
            }
            $roomGroups[$typeKey]['rooms'][] = $r;
        }
        $roomPriceOf = fn(array $r): float => (float)($r['customer_price'] ?? ($r['price'] ?? 0));
        foreach ($groupOrder as $gk) {
            $members = $roomGroups[$gk]['rooms'];
            usort($members, fn($a, $b) => $roomPriceOf($a) <=> $roomPriceOf($b));
            $roomGroups[$gk]['rooms'] = $members;
            $roomGroups[$gk]['representative'] = $members[0];
            $roomGroups[$gk]['fromPrice'] = $roomPriceOf($members[0]);
            $numbers = [];
            foreach ($members as $m) {
                $n = trim((string)($m['room_number'] ?? ''));
                if ($n !== '' && !in_array($n, $numbers, true)) {
                    $numbers[] = $n;
                }
            }
            $roomGroups[$gk]['numbers'] = $numbers;
            $roomGroups[$gk]['count'] = count($members);
        }
        $roomGroups = array_values(array_map(fn($gk) => $roomGroups[$gk], $groupOrder));
        // Category model: availability + occupancy + amenities are CATEGORY-level.
        // - availableCount: rooms with backend is_available (fallback: status not in maintenance set)
        // - bookRoom: cheapest AVAILABLE room (booking assigns a physical unit silently); cheapest overall as fallback
        // - occupancy: category max so "Sleeps N" reflects the room type, not one unit
        $maintenanceStatuses = ['maintenance', 'out_of_service', 'inactive', 'disabled'];
        $roomAvailable = function (array $r) use ($maintenanceStatuses): bool {
            if (array_key_exists('is_available', $r)) {
                return (bool)$r['is_available'];
            }
            return !in_array(strtolower(trim((string)($r['status'] ?? 'available'))), $maintenanceStatuses, true);
        };
        foreach ($roomGroups as &$g) {
            $members = $g['rooms'];
            $avail = array_values(array_filter($members, $roomAvailable));
            $g['availableRooms'] = $avail;
            $g['availableCount'] = count($avail);
            $bookPool = $avail !== [] ? $avail : $members;
            usort($bookPool, fn($a, $b) => $roomPriceOf($a) <=> $roomPriceOf($b));
            $g['bookRoom'] = $bookPool[0];
            $g['bookRoomId'] = (int)($bookPool[0]['id'] ?? 0);
            $maxAd = 1;
            $maxCh = 0;
            $maxCap = 0;
            $amenUnion = [];
            foreach ($members as $m) {
                $maxAd = max($maxAd, (int)($m['max_adults'] ?? ($m['capacity'] ?? 0)));
                $maxCh = max($maxCh, (int)($m['max_children'] ?? 0));
                $maxCap = max($maxCap, (int)($m['capacity'] ?? 0));
                $rawAm = $m['amenities'] ?? [];
                if (is_string($rawAm)) {
                    $dec = json_decode($rawAm, true);
                    $rawAm = is_array($dec) ? $dec : explode(',', $rawAm);
                }
                foreach ((array)$rawAm as $am) {
                    $amStr = trim((string)(is_array($am) ? ($am['name'] ?? '') : $am));
                    if ($amStr !== '' && !in_array($amStr, $amenUnion, true)) {
                        $amenUnion[] = $amStr;
                    }
                }
            }
            $g['maxAdults'] = max(1, $maxAd);
            $g['maxChildren'] = $maxCh;
            $g['maxCapacity'] = max($maxCap, $maxAd + $maxCh, 1);
            $g['amenities'] = $amenUnion;
        }
        unset($g);

        // Record in Recently Viewed Session
        try {
            $session = $this->getRequest()->getSession();
            $recent = $session->read('recently_viewed_stays') ?? [];
            if (!is_array($recent)) $recent = [];
            // filter out existing entry with same id
            $recent = array_values(array_filter($recent, fn($item) => (int)($item['id'] ?? 0) !== $propertyId));
            // $propTitle/$propCity/$propArea/$propPrice/$propRating/$reviewsCount and
            // $galleryImages are all defined in hotel-detail.php, never here. Every
            // `$x ?? fallback` below therefore evaluated the fallback, so this
            // session record stored invented values: a hardcoded 85,000 TSh
            // price, "Tanzania" and "Stay". Read the property record directly.
            $coverImg = $property['primary_image_url'] ?? ($property['image_url'] ?? '');
            array_unshift($recent, [
                'id' => $propertyId,
                'name' => $property['name'] ?? null,
                'city' => $property['city'] ?? null,
                'area' => $property['area'] ?? null,
                'price_per_night' => isset($property['price_per_night'])
                    ? (float)$property['price_per_night']
                    : null,
                'rating' => isset($property['rating']) ? (float)$property['rating'] : null,
                'reviews_count' => (int)($property['reviews_count'] ?? $property['review_count'] ?? 0),
                'image_url' => $coverImg,
                'viewed_at' => time(),
            ]);
            // keep up to 20 recent stays
            $session->write('recently_viewed_stays', array_slice($recent, 0, 20));
        } catch (\Throwable $e) {}

        $reviews = $this->staysService->getReviews($propertyId, (int)($queryParams['page'] ?? 1));

        if (empty($queryParams['destination'])) {
            $queryParams['destination'] = $property['city'] ?? '';
        }

        // Real city count for the breadcrumb (replaces hardcoded 520/326).
        // Cached 600s in StaysService — no extra backend hit on repeats.
        $cityPropertyCount = null;
        $bcCity = trim((string)($property['city'] ?? ''));
        if ($bcCity !== '') {
            try {
                $cityPropertyCount = $this->staysService->getCityCount($bcCity);
            } catch (\Throwable $e) {
                $cityPropertyCount = null;
            }
        }

        $this->set(compact('property', 'rooms', 'roomGroups', 'reviews', 'queryParams', 'propertyId', 'cityPropertyCount'));
        return $this->render('/Pages/hotel-detail');
    }

    /**
     * Destinations listing — deleted Explore page: now Apartment
     */
    public function destination01()
    {
        // Deleted explore page — redirect to Apartment (was Explore)
        return $this->redirect('/?property_type=Apartment');
    }
}
