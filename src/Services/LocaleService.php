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
}
