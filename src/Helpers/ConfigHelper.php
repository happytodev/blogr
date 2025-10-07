<?php

namespace Happytodev\Blogr\Helpers;

class ConfigHelper
{
    /**
     * Get a localized config value.
     * 
     * If the config value is an array with locale keys, return the value for current locale.
     * Otherwise, return the value as-is.
     * 
     * @param string $key The config key
     * @param string|null $locale The locale (defaults to app locale)
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function getLocalized(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        $value = config($key, $default);
        
        if (!is_array($value)) {
            return $value;
        }
        
        $locale = $locale ?? app()->getLocale();
        
        // If the value is an associative array with locale keys
        if (isset($value[$locale])) {
            return $value[$locale];
        }
        
        // Try default locale
        $defaultLocale = config('blogr.locales.default', 'en');
        if (isset($value[$defaultLocale])) {
            return $value[$defaultLocale];
        }
        
        // Try first available locale
        $availableLocales = config('blogr.locales.available', ['en']);
        foreach ($availableLocales as $availableLocale) {
            if (isset($value[$availableLocale])) {
                return $value[$availableLocale];
            }
        }
        
        // If it's not a locale array, return the array itself
        return $value;
    }
    
    /**
     * Get the reading time text format for the current locale.
     * 
     * @param int $minutes Reading time in minutes
     * @param string|null $locale The locale (defaults to app locale)
     * @return string
     */
    public static function getReadingTimeText(int $minutes, ?string $locale = null): string
    {
        $format = self::getLocalized('blogr.reading_time.text_format', $locale, 'Reading time: {time}');
        
        // If format is still null or empty, use default
        if (empty($format)) {
            $format = 'Reading time: {time}';
        }
        
        // Replace {time} placeholder with actual time
        return str_replace('{time}', $minutes . ' min', $format);
    }
}
