<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Service\MapService;
use Cake\View\Helper;

/**
 * MapHelper — View Helper exposing centralized Map Single Source of Truth
 */
class MapHelper extends Helper
{
    protected MapService $mapService;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->mapService = new MapService();
    }

    /**
     * Get resolved map configuration
     */
    public function config(): array
    {
        return $this->mapService->getMapConfig();
    }

    /**
     * Get static map preview URL for property cards & detail views
     */
    public function staticUrl(float $lat, float $lng, int $zoom = 13, int $width = 300, int $height = 300, string $label = ''): string
    {
        return $this->mapService->getStaticMapUrl($lat, $lng, $zoom, $width, $height, $label);
    }
}
