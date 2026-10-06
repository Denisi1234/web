<?php
declare(strict_types=1);

namespace App\Controller\Pages;

/**
 * PagesSamplesTrait — localhost-only sample stays for design review.
 * Rendered only when the backend returns nothing on a local hostname.
 * Production (fastnetstays.com) can never match the gate.
 */
trait PagesSamplesTrait
{
    /**
     * @return array<int, array<string, mixed>>
     */
    private function localSampleProperties(): array
    {
        $img = '/assets/img/hotel/hotel-1.jpg';
        // Varied cities, prices, and ratings so cards, filters, and map all render.
        return [
            ['id' => 9101, 'name' => 'Serena View Lodge', 'city' => 'Dar es Salaam', 'area' => 'Masaki', 'price_per_night' => 180000, 'customer_price_per_night' => 180000, 'rating' => 4.6, 'reviews_count' => 132, 'property_type' => 'Hotel', 'amenities' => ['Wifi', 'Pool', 'Breakfast'], 'images' => [$img], 'latitude' => -6.7924, 'longitude' => 39.2083],
            ['id' => 9102, 'name' => 'Meru Safari Camp', 'city' => 'Arusha', 'area' => 'Seke', 'price_per_night' => 240000, 'customer_price_per_night' => 240000, 'rating' => 4.8, 'reviews_count' => 89, 'property_type' => 'Safari Lodge', 'amenities' => ['Wifi', 'Parking', 'Restaurant'], 'images' => [$img], 'latitude' => -3.3869, 'longitude' => 36.6830],
            ['id' => 9103, 'name' => 'Stone Town House', 'city' => 'Zanzibar', 'area' => 'Stone Town', 'price_per_night' => 95000, 'customer_price_per_night' => 95000, 'rating' => 4.2, 'reviews_count' => 45, 'property_type' => 'Apartment', 'amenities' => ['Wifi', 'Kitchen'], 'images' => [$img], 'latitude' => -6.1659, 'longitude' => 39.2026],
            ['id' => 9104, 'name' => 'Capital Executive Suites', 'city' => 'Dodoma', 'area' => 'Central', 'price_per_night' => 120000, 'customer_price_per_night' => 120000, 'rating' => 3.9, 'reviews_count' => 21, 'property_type' => 'Hotel', 'amenities' => ['Wifi', 'Gym'], 'images' => [$img], 'latitude' => -6.1630, 'longitude' => 35.7516],
            ['id' => 9105, 'name' => 'Paje Beach Bungalows', 'city' => 'Zanzibar', 'area' => 'Paje', 'price_per_night' => 310000, 'customer_price_per_night' => 310000, 'rating' => 4.9, 'reviews_count' => 210, 'property_type' => 'Villa', 'amenities' => ['Wifi', 'Pool', 'Beach access', 'Breakfast'], 'images' => [$img], 'latitude' => -6.2733, 'longitude' => 39.8250],
        ];
    }

    /**
     * Swap empty backend results for design samples on localhost.
     * Gated inside useLocalSamples (local hostname) — never production.
     *
     * @return array{0: array, 1: bool} [properties, isSampleData]
     */
    private function maybeLocalSamples(array $properties): array
    {
        if (!$this->useLocalSamples($properties)) {
            return [$properties, false];
        }
        return [$this->localSampleProperties(), true];
    }

    private function useLocalSamples(array $properties): bool
    {
        if (!empty($properties)) {
            return false;
        }
        try {
            $host = strtolower((string)$this->getRequest()->getUri()->getHost());
        } catch (\Throwable $e) {
            return false;
        }
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
