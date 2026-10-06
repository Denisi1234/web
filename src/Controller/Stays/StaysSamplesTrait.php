<?php
declare(strict_types=1);

namespace App\Controller\Stays;

/**
 * StaysSamplesTrait — localhost-only sample property for design review.
 * Used only when the backend returns nothing on a local hostname.
 * Production (fastnetstays.com) can never match the gate.
 */
trait StaysSamplesTrait
{
    private function useLocalProperty(): bool
    {
        try {
            $host = strtolower((string)$this->getRequest()->getUri()->getHost());
        } catch (\Throwable $e) {
            return false;
        }
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function localSampleProperty(int $id): array
    {
        $img = '/assets/img/hotel/hotel-1.jpg';
        return [
            'id' => $id > 0 ? $id : 9101,
            'name' => 'Serena View Lodge',
            'city' => 'Dar es Salaam',
            'area' => 'Masaki',
            'address' => '123 Ocean Road, Masaki',
            'description' => 'Set along the ocean in Masaki, Serena View Lodge pairs modern rooms with warm Tanzanian hospitality. Wake up to sea views, enjoy breakfast on the terrace, and reach the city centre in minutes. Free Wi-Fi throughout, an outdoor pool, and friendly staff around the clock make every stay effortless.',
            'amenities' => ['Wifi', 'Pool', 'Breakfast', 'Parking', 'Air conditioning'],
            'rating' => 4.6,
            'reviews_count' => 132,
            'price_per_night' => 180000,
            'starting_price' => 180000,
            'latitude' => -6.7924,
            'longitude' => 39.2083,
            'image_url' => $img,
            'primary_image_url' => $img,
            'images' => [$img],
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'cancellation_policy' => 'Free cancellation up to 48 hours before check-in.',
        ];
    }
}
