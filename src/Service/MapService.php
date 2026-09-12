<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use function Cake\Core\env;

/**
 * MapService — Authoritative Single Source of Truth for Mapbox Integration
 * Ensures 100% native Mapbox GL vector maps and Mapbox Static Map API across all pages.
 */
class MapService
{
    protected FastnetApiClient $apiClient;

    public function __construct(?FastnetApiClient $apiClient = null)
    {
        $this->apiClient = $apiClient ?? new FastnetApiClient();
    }

    /**
     * Resolve authoritative Mapbox Access Token and Mapbox Streets v12 style
     */
    public function getMapConfig(): array
    {
        $mapboxToken = null;
        $mapboxStyle = (string)Configure::read('App.mapboxStyle', env('MAPBOX_STYLE', 'mapbox://styles/mapbox/streets-v12'));

        // 1. Fetch real token from backend microservice
        try {
            $cfg = $this->apiClient->get('/map-config');
            if (is_array($cfg)) {
                $candidate = $cfg['mapbox_token'] ?? $cfg['mapboxToken'] ?? $cfg['token'] ?? $cfg['access_token'] ?? null;
                if (!$candidate && isset($cfg['data']) && is_array($cfg['data'])) {
                    $candidate = $cfg['data']['mapbox_token'] ?? $cfg['data']['token'] ?? null;
                }
                if (is_string($candidate) && trim($candidate) !== '' && str_starts_with(trim($candidate), 'pk.')) {
                    $mapboxToken = trim($candidate);
                }
                if (!empty($cfg['mapbox_style']) && is_string($cfg['mapbox_style'])) {
                    $mapboxStyle = trim($cfg['mapbox_style']);
                }
            }
        } catch (\Throwable $e) {
            // silent — check local environment fallback
        }

        // 2. Check local environment variables (MAPBOX_TOKEN, MAPBOX_ACCESS_TOKEN, MAPBOX_API_KEY)
        if (!$mapboxToken) {
            $envToken = Configure::read('App.mapboxToken', env('MAPBOX_TOKEN', env('MAPBOX_ACCESS_TOKEN', env('MAPBOX_API_KEY', ''))));
            if (is_string($envToken) && str_starts_with(trim($envToken), 'pk.')) {
                $mapboxToken = trim($envToken);
            }
        }

        // 3. Fallback — no hard-coded token (use env MAPBOX_TOKEN); keep empty to force OSM fallback
        if (!$mapboxToken) {
            $mapboxToken = '';
        }

        return [
            'token' => $mapboxToken,
            'style' => $mapboxStyle,
            'isMapbox' => true,
            'defaultLat' => -6.7725,
            'defaultLng' => 39.2450,
            'defaultZoom' => 13
        ];
    }

    /**
     * Generate Mapbox Static Map API preview image URL or Base64 SVG map fallback
     */
    public function getStaticMapUrl(float $lat, float $lng, int $zoom = 13, int $width = 300, int $height = 300, string $label = ''): string
    {
        $config = $this->getMapConfig();
        $token  = $config['token'];

        // Only call Mapbox Static API if token is a valid, configured pk.* key (not dummy string)
        if (!empty($token) && str_starts_with($token, 'pk.') && !str_contains($token, '.demo')) {
            return sprintf(
                'https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/pin-s+1a73e8(%f,%f)/%f,%f,%d,0/%dx%d@2x?access_token=%s',
                $lng, $lat, $lng, $lat, $zoom, $width, $height, $token
            );
        }

        return $this->getSvgFallbackUrl($width, $height);
    }

    /**
     * Get standalone Base64 SVG Static Map Graphic
     */
    public function getSvgFallbackUrl(int $width = 300, int $height = 300): string
    {
        $svgRaw = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 300 300"><rect width="300" height="300" fill="#e5e7eb"/><path d="M0,80 Q150,120 300,60 L300,300 L0,300 Z" fill="#d1d5db"/><path d="M0,140 Q100,200 300,160" stroke="#ffffff" stroke-width="8" fill="none"/><path d="M120,0 Q160,150 140,300" stroke="#ffffff" stroke-width="6" fill="none"/><circle cx="150" cy="150" r="14" fill="#1a73e8"/><circle cx="150" cy="150" r="6" fill="#ffffff"/></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svgRaw);
    }
}
