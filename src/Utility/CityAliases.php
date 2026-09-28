<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * CityAliases
 *
 * Canonical Tanzanian destination matching shared by search filtering
 * (PagesController) and any future autocomplete/suggestion logic.
 *
 * UX-2: resolves misspellings + short codes (arusa→arusha, dsm→dar es salaam,
 * znz/stonetown→zanzibar, moshi→kilimanjaro, seronera→serengeti) so typos
 * never silently zero out results. Mirrors the client-side POPULAR aka keys
 * in element/Home/gh-search-bar.php — keep both lists in sync.
 */
class CityAliases
{
    /**
     * Canonical slug => accepted alias slugs (all lowercase alphanumeric).
     */
    public const GROUPS = [
        'arusha' => ['arusha', 'arusa', 'arushatown'],
        'daressalaam' => ['daressalaam', 'daressalam', 'daresalaam', 'dar', 'dsm'],
        'zanzibar' => ['zanzibar', 'znz', 'zanzibartown', 'stonetown'],
        'kilimanjaro' => ['kilimanjaro', 'moshi'],
        'serengeti' => ['serengeti', 'seronera'],
        'mwanza' => ['mwanza'],
        'dodoma' => ['dodoma'],
        'ngorongoro' => ['ngorongoro'],
    ];

    /**
     * Normalize any raw string to a comparable slug.
     */
    public static function slug(string $value): string
    {
        return (string)preg_replace('/[^a-z0-9]/', '', strtolower(trim($value)));
    }

    /**
     * Map a raw destination to its canonical slug (unknown slugs pass through).
     */
    public static function canonicalize(string $destination): string
    {
        $slug = static::slug($destination);
        foreach (static::GROUPS as $canonical => $aliases) {
            if (in_array($slug, $aliases, true)) {
                return $canonical;
            }
        }
        return $slug;
    }

    /**
     * True when a property city (or area) belongs to the requested destination group.
     */
    public static function matches(string $destination, string $city, string $area = ''): bool
    {
        $destSlug = static::canonicalize($destination);
        $citySlug = static::slug(trim(explode(',', strtolower(trim($city)))[0]));
        // Dar es Salaam typo tolerance: any dar-* destination matches any dar-* city and vice versa never leaks
        $isDarDest = str_contains($destSlug, 'dar');
        $isDarCity = str_contains($citySlug, 'dar');
        if ($isDarDest && $isDarCity) {
            return true;
        }
        if ($isDarDest !== $isDarCity) {
            return false;
        }
        if ($citySlug === $destSlug) {
            return true;
        }
        foreach (static::GROUPS as $aliases) {
            if (in_array($destSlug, $aliases, true) && in_array($citySlug, $aliases, true)) {
                return true;
            }
        }
        $areaSlug = static::slug(trim(explode(',', strtolower(trim($area)))[0]));
        if ($areaSlug !== '' && ($areaSlug === $destSlug || $areaSlug === strtolower(trim($destination)))) {
            return true;
        }
        return false;
    }
}
