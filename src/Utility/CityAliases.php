<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * CityAliases
 *
 * Canonical Tanzanian destination matching shared by search filtering
 * (PagesController) and autocomplete.
 * Resolves misspellings + short codes (arusa→Arusha, dsm→Dar es Salaam,
 * znz/stonetown→Zanzibar, moshi→Kilimanjaro) case-insensitively so
 * ARUSHA / arusha / ArU all return the same Arusha results.
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
     * Canonical slug => proper display name sent to backend + shown in UI.
     * Guarantees arusha / ARUSHA / ArU all resolve to "Arusha".
     */
    public const LABELS = [
        'arusha' => 'Arusha',
        'daressalaam' => 'Dar es Salaam',
        'zanzibar' => 'Zanzibar',
        'kilimanjaro' => 'Kilimanjaro',
        'serengeti' => 'Serengeti',
        'mwanza' => 'Mwanza',
        'dodoma' => 'Dodoma',
        'ngorongoro' => 'Ngorongoro',
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
     * Case-insensitive: ARUSHA, Arusha, arusha → arusha.
     * Prefix-tolerant: ArU → arusha, Dar → daressalaam, Zan → zanzibar (min 2 chars).
     */
    public static function canonicalize(string $destination): string
    {
        $slug = static::slug($destination);
        if ($slug === '') {
            return '';
        }
        foreach (static::GROUPS as $canonical => $aliases) {
            if (in_array($slug, $aliases, true)) {
                return $canonical;
            }
        }
        // Prefix fallback — "aru" → arusha, "zan" → zanzibar, "mw" → mwanza.
        // Requires min 2 chars to avoid "a" matching everything.
        if (mb_strlen($slug) >= 2) {
            foreach (static::GROUPS as $canonical => $aliases) {
                if (str_starts_with($canonical, $slug)) {
                    return $canonical;
                }
                foreach ($aliases as $alias) {
                    if (str_starts_with($alias, $slug) || str_starts_with($slug, $alias)) {
                        return $canonical;
                    }
                }
            }
        }
        return $slug;
    }

    /**
     * Resolve any user input (any case, prefix, alias) to the proper display
     * name for backend queries. Returns null when input is empty/unknown —
     * callers then keep the raw input so the backend still searches it.
     * ARUSHA / arusha / ArU / arusa → "Arusha".
     */
    public static function resolveLabel(string $destination): ?string
    {
        $canon = static::canonicalize($destination);
        if ($canon === '') {
            return null;
        }
        return static::LABELS[$canon] ?? null;
    }

    /**
     * True when a property city (or area) belongs to the requested destination group.
     * Fully case-insensitive + prefix-tolerant: ARUSHA / arusha / ArU all match Arusha.
     */
    public static function matches(string $destination, string $city, string $area = ''): bool
    {
        $destRaw = trim($destination);
        if ($destRaw === '') {
            return true;
        }
        $destSlug = static::canonicalize($destRaw);
        $citySlug = static::slug(trim(explode(',', $city)[0]));
        if ($citySlug === '') {
            return false;
        }
        // Exact canonical match (covers ARUSHA vs arusha — both → arusha).
        if ($citySlug === $destSlug) {
            return true;
        }
        // Dar es Salaam typo tolerance: any dar-* destination matches any dar-* city and vice versa never leaks
        $isDarDest = str_contains($destSlug, 'dar');
        $isDarCity = str_contains($citySlug, 'dar');
        if ($isDarDest && $isDarCity) {
            return true;
        }
        if ($isDarDest !== $isDarCity) {
            return false;
        }
        foreach (static::GROUPS as $canonical => $aliases) {
            $destIn = $destSlug === $canonical || in_array($destSlug, $aliases, true);
            $cityIn = $citySlug === $canonical || in_array($citySlug, $aliases, true);
            if ($destIn && $cityIn) {
                return true;
            }
        }
        // Prefix match — "aru" matches "arusha" (min 2 chars, either direction).
        // Lets partial typing ArU return the same set as ARUSHA.
        $rawDestSlug = static::slug($destRaw);
        if (mb_strlen($rawDestSlug) >= 2 && mb_strlen($citySlug) >= 2) {
            if (str_starts_with($citySlug, $rawDestSlug) || str_starts_with($rawDestSlug, $citySlug)) {
                return true;
            }
            if (str_starts_with($destSlug, $citySlug) || str_starts_with($citySlug, $destSlug)) {
                return true;
            }
        }
        $areaSlug = static::slug(trim(explode(',', $area)[0]));
        if ($areaSlug !== '') {
            if ($areaSlug === $destSlug || $areaSlug === $rawDestSlug) {
                return true;
            }
            if (mb_strlen($rawDestSlug) >= 2 && (str_starts_with($areaSlug, $rawDestSlug) || str_starts_with($rawDestSlug, $areaSlug))) {
                return true;
            }
        }
        return false;
    }
}
