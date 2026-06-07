<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Illuminate\Support\Facades\Cache;

class LocaleService
{
    protected string $cacheKey = 'blogr.locales.available';

    public function getAvailable(): array
    {
        $autoDetect = config('blogr.locales.auto_detect', false);
        $disabled = config('blogr.locales.disabled', []);

        if (!$autoDetect) {
            $locales = config('blogr.locales.available', ['en']);

            return array_values(array_diff($locales, $disabled));
        }

        return Cache::rememberForever($this->cacheKey, function () use ($disabled) {
            $blogLocales = BlogPostTranslation::query()
                ->whereHas('post', fn ($q) => $q->where('is_published', true))
                ->distinct()
                ->pluck('locale')
                ->toArray();

            $cmsLocales = CmsPageTranslation::query()
                ->whereHas('page', fn ($q) => $q->where('is_published', true))
                ->distinct()
                ->pluck('locale')
                ->toArray();

            $locales = array_unique(array_merge($blogLocales, $cmsLocales));
            $locales = array_values(array_diff($locales, $disabled));

            sort($locales);

            $restrict = config('blogr.locales.restrict', []);

            if (!empty($restrict)) {
                $locales = array_values(array_intersect($locales, $restrict));
            }

            return !empty($locales) ? $locales : [config('blogr.locales.default', 'en')];
        });
    }

    public function flushCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    public function localeLabel(string $locale): string
    {
        $names = [
            'en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'de' => 'Deutsch',
            'it' => 'Italiano', 'pt' => 'Português', 'pl' => 'Polski', 'ru' => 'Русский',
            'nl' => 'Nederlands', 'el' => 'Ελληνικά', 'no' => 'Norsk', 'da' => 'Dansk',
            'sv' => 'Svenska', 'fi' => 'Suomi', 'cs' => 'Čeština', 'sk' => 'Slovenčina',
            'hu' => 'Magyar', 'ro' => 'Română', 'bg' => 'Български', 'sr' => 'Српски',
            'hr' => 'Hrvatski', 'sl' => 'Slovenščina', 'et' => 'Eesti', 'lv' => 'Latviešu',
            'lt' => 'Lietuvių', 'uk' => 'Українська', 'ja' => '日本語', 'zh' => '中文',
            'ko' => '한국어', 'ar' => 'العربية', 'hi' => 'हिन्दी', 'tr' => 'Türkçe',
            'th' => 'ไทย', 'vi' => 'Tiếng Việt', 'id' => 'Bahasa Indonesia',
        ];

        $name = $names[$locale] ?? strtoupper($locale);

        return "{$name} ({$locale})";
    }
}
