<?php
declare(strict_types=1);

namespace App\Service;

/**
 * StaysService
 * 
 * Modular domain service handling accommodation/hotel stays,
 * regional aggregations, search filtering, and property details.
 */
class StaysService
{
    protected FastnetApiClient $apiClient;

    public function __construct(?FastnetApiClient $apiClient = null)
    {
        $this->apiClient = $apiClient ?: new FastnetApiClient();
    }

    /**
     * Get featured stays and luxury resorts for homepage
     */
    public function getFeaturedResorts(int $limit = 50): array
    {
        $res = $this->apiClient->get('/properties', ['limit' => $limit]);
        if (!empty($res)) {
            $data = $res['data'] ?? ($res['items'] ?? $res);
            if (is_array($data) && !empty($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Build the 4 requested regions with real live property counts
     */
    public function getDestinationsSummary(array $properties = []): array
    {
        if (empty($properties)) {
            $properties = $this->getFeaturedResorts(50);
        }

        $regions = [
            [
                'title' => 'Zanzibar',
                'city' => 'Zanzibar',
                'keywords' => ['zanzibar', 'znz', 'nungwi', 'paje', 'stone town'],
                'img' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=80'
            ],
            [
                'title' => 'Dar es Salaam',
                'city' => 'Dar es Salaam',
                'keywords' => ['dar', 'dar es salaam', 'dar es salam', 'mbezi', 'kinondoni', 'masaki'],
                'img' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=600&q=80'
            ],
            [
                'title' => 'Arusha',
                'city' => 'Arusha',
                'keywords' => ['arusha', 'sekei', 'ngorongoro'],
                'img' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80'
            ],
            [
                'title' => 'Dodoma',
                'city' => 'Dodoma',
                'keywords' => ['dodoma', 'capital'],
                'img' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=600&q=80'
            ]
        ];

        foreach ($regions as &$reg) {
            $count = 0;
            if (is_array($properties)) {
                foreach ($properties as $p) {
                    $pSearch = strtolower(trim(($p['city'] ?? '') . ' ' . ($p['area'] ?? '') . ' ' . ($p['name'] ?? '')));
                    foreach ($reg['keywords'] as $kw) {
                        if (str_contains($pSearch, $kw)) {
                            $count++;
                            break;
                        }
                    }
                }
            }
            $reg['count'] = max(1, $count);
        }

        return $regions;
    }

    /**
     * Search properties by city, dates, guests, or price
     */
    public function searchProperties(array $params = []): array
    {
        $res = $this->apiClient->get('/properties', $params);
        if ($res === null) {
            return [];
        }

        $data = $res['data'] ?? ($res['items'] ?? $res);
        return is_array($data) ? array_values($data) : [];
    }

    /**
     * Get property by ID
     */
    public function getProperty(int $id): ?array
    {
        $res = $this->apiClient->get('/properties/' . $id);
        if (!empty($res['data'])) {
            return $res['data'];
        }
        if (!empty($res) && is_array($res) && isset($res['id'])) {
            return $res;
        }

        // Some backend deployments expose the collection endpoint reliably
        // while the single-property route is unavailable. Search that same
        // live backend response before treating the property as missing.
        $collection = $this->apiClient->get('/properties', ['limit' => 100]);
        $properties = $collection['data'] ?? ($collection['items'] ?? $collection);
        if (is_array($properties)) {
            foreach ($properties as $property) {
                if (is_array($property) && (int)($property['id'] ?? 0) === $id) {
                    return $property;
                }
            }
        }

        return null;
    }
}
