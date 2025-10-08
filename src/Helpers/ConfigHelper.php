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
     * @param int|null $minutes Reading time in minutes (null if not calculated)
     * @param string|null $locale The locale (defaults to app locale)
     * @return string
     */
    public static function getReadingTimeText(?int $minutes, ?string $locale = null): string
    {
        $format = self::getLocalized('blogr.reading_time.text_format', $locale, 'Reading time: {time}');
        
        // If format is still null or empty, use default
        if (empty($format)) {
            $format = 'Reading time: {time}';
        }
        
        // If no reading time or less than 1 minute, show "< 1 minute"
        if ($minutes === null || $minutes <= 0) {
            return str_replace('{time}', '< 1 min', $format);
        }
        
        // Replace {time} placeholder with actual time
        return str_replace('{time}', $minutes . ' min', $format);
    }
    
    /**
     * Get the SEO site name for the current locale.
     * 
     * @param string|null $locale The locale (defaults to app locale)
     * @return string
     */
    public static function getSeoSiteName(?string $locale = null): string
    {
        return self::getLocalized('blogr.seo.site_name', $locale, env('APP_NAME', 'My Blog'));
    }
    
    /**
     * Get the SEO default title for the current locale.
     * 
     * @param string|null $locale The locale (defaults to app locale)
     * @return string
     */
    public static function getSeoDefaultTitle(?string $locale = null): string
    {
        return self::getLocalized('blogr.seo.default_title', $locale, 'Blog');
    }
    
    /**
     * Get the SEO default description for the current locale.
     * 
     * @param string|null $locale The locale (defaults to app locale)
     * @return string
     */
    public static function getSeoDefaultDescription(?string $locale = null): string
    {
        return self::getLocalized('blogr.seo.default_description', $locale, 'Discover our latest articles and insights');
    }
    
    /**
     * Get the SEO default keywords for the current locale.
     * 
     * @param string|null $locale The locale (defaults to app locale)
     * @return string
     */
    public static function getSeoDefaultKeywords(?string $locale = null): string
    {
        return self::getLocalized('blogr.seo.default_keywords', $locale, 'blog, articles, news, insights');
    }
}
