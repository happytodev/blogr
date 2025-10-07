<?php

namespace Happytodev\Blogr\Helpers;

class LocaleHelper
{
    /**
     * Get the current locale from the request or app
     */
    public static function currentLocale(): string
    {
        return request()->attributes->get('locale') ?? app()->getLocale();
    }

    /**
     * Generate a localized route URL
     */
    public static function route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? self::currentLocale();
        $defaultLocale = config('blogr.locales.default', 'en');
        
        // Add locale to parameters
        $parameters = array_merge(['locale' => $locale], $parameters);
        
        $url = route($name, $parameters);
        
        return $url;
    }

    /**
     * Get available locales
     */
    public static function availableLocales(): array
    {
        return config('blogr.locales.available', ['en']);
    }

    /**
     * Get alternate URLs for hreflang tags
     */
    public static function alternateUrls(string $routeName, array $parameters = []): array
    {
        $urls = [];
        
        foreach (self::availableLocales() as $locale) {
            $urls[$locale] = self::route($routeName, $parameters, $locale);
        }
        
        return $urls;
    }

    /**
     * Generate hreflang meta tags
     */
    public static function hreflangTags(string $routeName, array $parameters = []): string
    {
        $tags = [];
        $alternates = self::alternateUrls($routeName, $parameters);
        
        foreach ($alternates as $locale => $url) {
            $tags[] = sprintf('<link rel="alternate" hreflang="%s" href="%s" />', $locale, $url);
        }
        
        // Add x-default
        $defaultLocale = config('blogr.locales.default', 'en');
        $tags[] = sprintf('<link rel="alternate" hreflang="x-default" href="%s" />', $alternates[$defaultLocale]);
        
        return implode("\n    ", $tags);
    }
}
