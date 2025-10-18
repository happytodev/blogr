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
}
