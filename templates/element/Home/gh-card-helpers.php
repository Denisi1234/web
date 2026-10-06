<?php
/**
 * fastnetstays.com — Hotel result cards.
 * Photo + name/location/rating/price + View prices CTA.
 */
if (!function_exists('ghNormalizeImgUrl')) {
    function ghNormalizeImgUrl(string $url): string {
        $url = trim($url);
        if ($url === '') return '';
        // If image points to local or relative storage in production, resolve with configured backend host
        if (str_starts_with($url, '/storage/') || str_contains($url, '127.0.0.1:8000/storage') || str_contains($url, 'localhost/storage')) {
            $apiBase = (string)\Cake\Core\Configure::read('App.backendApiUrl', \Cake\Core\env('BACKEND_API_URL', 'http://127.0.0.1:8000/api'));
            $backendHost = rtrim(preg_replace('#/api/?$#', '', $apiBase), '/');
            $storagePath = substr($url, strpos($url, '/storage/'));
            return $backendHost . $storagePath;
        }
        return $url;
    }
}
if (!function_exists('ghPropImage2')) {
    function ghPropImage2(array $p): string {
        foreach (['image_url','primary_image_url','cover_image','thumbnail'] as $k) {
            if (!empty($p[$k])) return ghNormalizeImgUrl((string)$p[$k]);
        }
        return '';
    }
}
if (!function_exists('ghPropImages')) {
    function ghPropImages(array $p): array {
        $out = [];
        // Primary images array (real deploy will have $p['images'] as JSON or array of urls/objects)
        $raw = $p['images'] ?? $p['gallery'] ?? $p['photos'] ?? null;
        if (is_string($raw)) { $d = json_decode($raw, true); if (is_array($d)) $raw = $d; else $raw = null; }
        if (is_array($raw)) {
            foreach ($raw as $it) {
                $u = is_array($it) ? ($it['url'] ?? $it['image_url'] ?? $it['src'] ?? '') : (string)$it;
                $u = ghNormalizeImgUrl($u);
                if ($u !== '' && !in_array($u, $out, true)) $out[] = $u;
                if (count($out) >= 8) break;
            }
        }
        // fallback single fields if no gallery
        if (empty($out)) {
            foreach (['primary_image_url','image_url','cover_image','thumbnail'] as $k) {
                if (!empty($p[$k])) {
                    $u = ghNormalizeImgUrl((string)$p[$k]);
                    if ($u !== '' && !in_array($u, $out, true)) $out[] = $u;
                }
            }
        }
        return $out;
    }
}
?>
