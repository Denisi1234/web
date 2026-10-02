<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FastnetApiClient;
use Cake\Cache\Cache;

/**
 * Destination Controller — backend-driven travel hubs.
 * All destination data comes from the backend API (/destinations);
 * nothing is hardcoded in the frontend.
 */
class DestinationController extends AppController
{
    public function detail($slug = null)
    {
        // Backend is source of truth — destinations list comes from the API.
        // Cached 5 min; empty backend means empty page (no hardcoded trips).
        $items = [];
        try {
            $cached = Cache::read('backend_destinations', 'default');
            if (is_array($cached) && isset($cached['exp'], $cached['data']) && $cached['exp'] > time()) {
                $items = $cached['data'];
            } else {
                $res = (new FastnetApiClient())->get('/destinations', [], [], 4);
                if (is_array($res)) {
                    $items = $res['data'] ?? ($res['items'] ?? []);
                }
                if (!is_array($items)) {
                    $items = [];
                }
                try {
                    Cache::write('backend_destinations', ['exp' => time() + 300, 'data' => $items], 'default');
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
            $items = [];
        }

        $trips = [];
        foreach ($items as $d) {
            if (!is_array($d)) {
                continue;
            }
            $trips[] = [
                'id' => $d['id'] ?? null,
                'img' => $d['image_url'] ?? ($d['img'] ?? ''),
                'title' => $d['name'] ?? ($d['title'] ?? ''),
                'price' => $d['price'] ?? ($d['price_per_night'] ?? ''),
                'for' => $d['for'] ?? '',
                'city' => $d['city'] ?? '',
            ];
        }

        $article = null;
        if ($slug !== null) {
            foreach ($trips as $item) {
                $slugifiedTitle = strtolower(str_replace(' ', '-', (string)($item['title'] ?? '')));
                if ($slugifiedTitle === strtolower($slug) || strtolower((string)($item['city'] ?? '')) === strtolower($slug)) {
                    $article = $item;
                    break;
                }
            }
        }
        if ($article === null && !empty($trips[0])) {
            $article = $trips[0];
        }

        $this->set(compact('article', 'trips'));
        $this->render('/Pages/destination-detail');
    }
}