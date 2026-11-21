<?php

namespace Happytodev\Blogr\Helpers;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogSeries;
use Illuminate\Support\Facades\Route;

/**
 * Resolves the public URL of a blog post.
 *
 * A post that belongs to a published series is served under that series
 * (series/{seriesSlug}/{postSlug}); every other post keeps the flat blog URL
 * (/{postSlug}). The nested URL is the canonical one: the flat URL of a series
 * post answers with a 301 to it (see BlogController::show()).
 *
 * Always use this helper instead of building a blog.show route by hand, so an
 * internal link never points to a URL that only redirects.
 */
class PostUrlHelper
{
    /**
     * Canonical URL of a post for the given locale.
     */
    public static function forPost(BlogPost $post, ?string $locale = null): string
    {
        $locale = self::resolveLocale($locale);

        return self::nestedForPost($post, $locale) ?? self::flatForPost($post, $locale);
    }

    /**
     * Canonical URL of a translation, resolved from its parent post.
     */
    public static function forTranslation(BlogPostTranslation $translation, ?string $locale = null): string
    {
        $locale = self::resolveLocale($locale ?? $translation->locale);
        $post = $translation->post;

        if (! $post instanceof BlogPost) {
            return self::flatUrl($locale, self::stringOrNull($translation->slug));
        }

        return self::forPost($post, $locale);
    }

    /**
     * The series-nested URL, or null when the post has no usable series page
     * for that locale (no series, series not published, no slug, no route).
     */
    public static function nestedForPost(BlogPost $post, ?string $locale = null): ?string
    {
        $locale = self::resolveLocale($locale);

        $series = $post->relationLoaded('series') ? $post->getRelation('series') : $post->series()->first();

        if (! $series instanceof BlogSeries) {
            return null;
        }

        // Only point to a series the public series route actually serves: that
        // route uses the published() scope, which excludes a series without a
        // published_at. BlogSeries::isPublished() treats a null published_at as
        // published, so it cannot be used here without sending readers to a 404.
        if ($series->published_at === null || $series->published_at->isFuture()) {
            return null;
        }

        if (! Route::has('blog.series.post')) {
            return null;
        }

        $postSlug = self::postSlug($post, $locale);
        $seriesSlug = self::stringOrNull($series->getTranslatedSlug($locale));

        if ($postSlug === null || $seriesSlug === null) {
            return null;
        }

        $parameters = [
            'seriesSlug' => $seriesSlug,
            'slug' => $postSlug,
        ];

        if (self::localesEnabled()) {
            $parameters['locale'] = $locale;
        }

        return route('blog.series.post', $parameters);
    }

    /**
     * The flat blog URL of a post, used when it is not part of a published series.
     */
    public static function flatForPost(BlogPost $post, ?string $locale = null): string
    {
        $locale = self::resolveLocale($locale);

        return self::flatUrl($locale, self::postSlug($post, $locale));
    }

    /**
     * Build the flat blog.show URL for a slug.
     */
    public static function flatUrl(string $locale, ?string $slug): string
    {
        $parameters = ['slug' => (string) $slug];

        if (self::localesEnabled()) {
            $parameters['locale'] = $locale;
        }

        return route('blog.show', $parameters);
    }

    /**
     * Slug of the post for the given locale, with a fallback on its default translation.
     */
    public static function postSlug(BlogPost $post, ?string $locale = null): ?string
    {
        $locale = self::resolveLocale($locale);
        $translation = $post->translate($locale) ?? $post->getDefaultTranslation();

        if ($translation instanceof BlogPostTranslation) {
            $slug = self::stringOrNull($translation->slug);

            if ($slug !== null) {
                return $slug;
            }
        }

        return self::stringOrNull($post->slug);
    }

    protected static function localesEnabled(): bool
    {
        return (bool) config('blogr.locales.enabled', false);
    }

    protected static function resolveLocale(?string $locale): string
    {
        $locale = $locale ?? app()->getLocale();

        if ($locale !== '') {
            return $locale;
        }

        $default = config('blogr.locales.default');

        return is_string($default) && $default !== '' ? $default : 'en';
    }

    /**
     * Normalize a value that is expected to be a non-empty slug.
     */
    protected static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
