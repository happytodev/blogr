<?php

namespace Happytodev\Blogr\Helpers;

class ColorHelper
{
    /**
     * Generate dark mode variant from a light Tailwind color class
     * 
     * @param string $lightColor The light color class (e.g., 'bg-blue-50')
     * @return string The color with dark mode variant (e.g., 'bg-blue-50 dark:bg-blue-900')
     */
    public static function generateDarkMode(string $lightColor): string
    {
        if (empty($lightColor) || str_contains($lightColor, 'dark:')) {
            return $lightColor; // Already has dark mode or is empty
        }

        // Parse the color class
        if (preg_match('/(bg|text|border)-(\w+)-(\d+)/', $lightColor, $matches)) {
            $prefix = $matches[1]; // bg, text, or border
            $color = $matches[2];  // blue, red, etc.
            $shade = (int)$matches[3]; // 50, 100, etc.

            // Invert shade for dark mode
            // 50 -> 900, 100 -> 800, 200 -> 700, etc.
            $darkShade = $shade <= 500 ? 1000 - $shade : $shade;

            return "{$lightColor} dark:{$prefix}-{$color}-{$darkShade}";
        }

        // For simple colors without shades (bg-white, bg-black)
        if (str_contains($lightColor, 'bg-white')) {
            return str_replace('bg-white', 'bg-white dark:bg-gray-800', $lightColor);
        }
        if (str_contains($lightColor, 'bg-black')) {
            return str_replace('bg-black', 'bg-black dark:bg-gray-100', $lightColor);
        }

        // Return as-is if we can't parse it
        return $lightColor;
    }

    /**
     * Remove dark mode classes from a color string
     * 
     * @param string $colorWithDark Color string potentially with dark mode classes
     * @return string Color string without dark mode classes
     */
    public static function removeDarkMode(string $colorWithDark): string
    {
        return preg_replace('/\s*dark:[^\s]+/', '', $colorWithDark);
    }

    /**
     * Extract only dark mode classes from a color string
     * 
     * @param string $colorWithDark Color string potentially with dark mode classes
     * @return string Only the dark mode classes
     */
    public static function extractDarkMode(string $colorWithDark): string
    {
        if (preg_match('/dark:[^\s]+/', $colorWithDark, $matches)) {
            return $matches[0];
        }
        return '';
    }

    /**
     * Adjust brightness of a hex color
     * 
     * @param string $hex Color in hex format (e.g., #FF0000)
     * @param int $percent Percentage to adjust (-100 to +100)
     * @return string Adjusted color in hex format
     */
    public static function adjustBrightness(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));

        // Convert to HSL
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r:
                    $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6;
                    break;
                case $g:
                    $h = (($b - $r) / $d + 2) / 6;
                    break;
                case $b:
                    $h = (($r - $g) / $d + 4) / 6;
                    break;
            }
        }

        // Adjust brightness
        $l = max(0, min(1, $l + ($percent / 100)));

        // Convert back to RGB
        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = self::hslToRgb($p, $q, $h + 1 / 3);
            $g = self::hslToRgb($p, $q, $h);
            $b = self::hslToRgb($p, $q, $h - 1 / 3);
        }

        return '#' . implode('', array_map(fn($c) => str_pad(dechex((int)round($c * 255)), 2, '0', STR_PAD_LEFT), [$r, $g, $b]));
    }

    /**
     * Convert HSL component to RGB
     */
    private static function hslToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }
        return $p;
    }

    /**
     * Convert hex color to RGB format
     * 
     * @param string $hex Color in hex format
     * @return string Color in rgb(r,g,b) format
     */
    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));
        return "rgb($r, $g, $b)";
    }

    /**
     * Convert RGB values to hex format
     * 
     * @param int $r Red value (0-255)
     * @param int $g Green value (0-255)
     * @param int $b Blue value (0-255)
     * @return string Color in hex format
     */
    public static function rgbToHex(int $r, int $g, int $b): string
    {
        return '#' . str_pad(dechex((int)round($r)), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex((int)round($g)), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex((int)round($b)), 2, '0', STR_PAD_LEFT);
    }
}
