<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * TextFormatter
 * 
 * Intelligent text normalization and title casing utility.
 * Cleans user-submitted strings (e.g. lowercase lodge names, missing capitalization,
 * missing spaces after commas, unformatted acronyms, and well-known location spellings).
 */
class TextFormatter
{
    /**
     * Words that should stay lowercase unless they are the first word in a phrase.
     */
    protected static array $minorWords = [
        'es', 'la', 'de', 'and', 'the', 'of', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'van', 'del'
    ];

    /**
     * Standard dictionary corrections for common Tanzanian destinations & terms.
     */
    protected static array $dictionary = [
        '/\bdar\s+es\s+salam\b/i' => 'Dar es Salaam',
        '/\bdar\s+es\s+salaam\b/i' => 'Dar es Salaam',
        '/\bstone\s+town\b/i' => 'Stone Town',
        '/\boyster\s*bay\b/i' => 'Oyster Bay',
        '/\bmbezi\s+beach\b/i' => 'Mbezi Beach',
        '/\bsinza\s+makabulini\b/i' => 'Sinza Makabulini',
        '/\bwi\s*[- ]?\s*fi\b/i' => 'Wi-Fi',
        '/\b(tv|led|ac|wc|hdtv|lcd)\b/i' => 'strtoupper',
    ];

    /**
     * Convert any text (all-caps, all-lowercase, sloppy spaces) into a polished Title.
     */
    public static function formatTitle(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Fix missing space after commas: "45 sekei road,arusha" -> "45 sekei road, arusha"
        $text = (string)preg_replace('/,(\S)/u', ', $1', $text);
        // Normalize multiple spaces into single space
        $text = (string)preg_replace('/\s+/u', ' ', $text);

        // Split words and apply intelligent title capitalization
        $words = explode(' ', $text);
        $formatted = [];
        foreach ($words as $idx => $w) {
            $w = trim($w);
            if ($w === '') continue;

            $lower = mb_strtolower($w, 'UTF-8');
            // If word contains hyphen (e.g. "high-speed" or "wi-fi"), capitalize each subpart
            if (str_contains($w, '-')) {
                $subParts = explode('-', $w);
                $subFormatted = array_map(function($sub, $subIdx) {
                    $subLower = mb_strtolower($sub, 'UTF-8');
                    if ($subIdx > 0 && in_array($subLower, self::$minorWords, true)) {
                        return $subLower;
                    }
                    return mb_convert_case($subLower, MB_CASE_TITLE, 'UTF-8');
                }, $subParts, array_keys($subParts));
                $formatted[] = implode('-', $subFormatted);
            } elseif ($idx > 0 && in_array($lower, self::$minorWords, true)) {
                $formatted[] = $lower;
            } else {
                $formatted[] = mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
            }
        }
        $result = implode(' ', $formatted);

        // Apply specific dictionary replacements
        foreach (self::$dictionary as $pattern => $replacement) {
            if ($replacement === 'strtoupper') {
                $result = (string)preg_replace_callback($pattern, fn($m) => strtoupper($m[0]), $result);
            } else {
                $result = (string)preg_replace($pattern, (string)$replacement, $result);
            }
        }

        return $result;
    }

    /**
     * Format a full location string from area, city, or address.
     */
    public static function formatLocation(?string $area, ?string $city = null): string
    {
        $parts = [];
        if (!empty($area)) {
            $parts[] = self::formatTitle($area);
        }
        if (!empty($city)) {
            $fmtCity = self::formatTitle($city);
            if (empty($parts) || !str_contains(strtolower($parts[0]), strtolower($fmtCity))) {
                $parts[] = $fmtCity;
            }
        }
        return implode(', ', $parts);
    }
}
