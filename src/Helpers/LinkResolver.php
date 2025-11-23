<?php

namespace Happytodev\Blogr\Helpers;

use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;

/**
 * Resolve dynamic links based on link type and reference IDs
 * Converts CMS block data into actual URLs
 */
class LinkResolver
{
    /**
     * Resolve a link from block data
     *
     * @param array $data Block data containing link_type and related fields
     * @param string $linkTypeKey The key containing the link type (e.g., 'cta_link_type')
     * @param string $urlKey The key containing the URL (e.g., 'cta_url')
     * @param string $categoryIdKey The key containing the category ID (e.g., 'cta_category_id')
     * @param string $cmsPageIdKey The key containing the CMS page ID (e.g., 'cta_cms_page_id')
     * @return string|null The resolved URL or null if not found
     */
    public static function resolve(
        array $data,
        string $linkTypeKey = 'link_type',
        string $urlKey = 'url',
        string $categoryIdKey = 'category_id',
        string $cmsPageIdKey = 'cms_page_id'
    ): ?string {
        $linkType = $data[$linkTypeKey] ?? 'external';

        return match ($linkType) {
            'external' => $data[$urlKey] ?? null,
            'blog' => self::resolveBlogLink(),
            'category' => self::resolveCategoryLink($data[$categoryIdKey] ?? null),
            'cms_page' => self::resolveCmsPageLink($data[$cmsPageIdKey] ?? null),
            default => null,
        };
    }

    /**
     * Resolve blog home link
     */
    private static function resolveBlogLink(): ?string
    {
        try {
            return route('blogr.blog.index');
        } catch (\Exception $e) {
            // Route not available (e.g., in tests)
            return null;
        }
    }

    /**
     * Resolve category link
     */
    private static function resolveCategoryLink(?int $categoryId): ?string
    {
        if (!$categoryId) {
            return null;
        }

        $category = Category::find($categoryId);
        if (!$category) {
            return null;
        }

        try {
            return route('blogr.blog.category', ['category' => $category]);
        } catch (\Exception $e) {
            // Route not available (e.g., in tests)
            return null;
        }
    }

    /**
     * Resolve CMS page link
     */
    private static function resolveCmsPageLink(?int $pageId): ?string
    {
        if (!$pageId) {
            return null;
        }

        $page = CmsPage::find($pageId);
        if (!$page) {
            return null;
        }

        try {
            // Get the first translation to access the slug
            $translation = $page->translations()->first();
            if (!$translation) {
                return null;
            }

            // Check if locales are enabled
            $localesEnabled = config('blogr.locales.enabled', false);
            
            if ($localesEnabled) {
                return route('cms.page.show', [
                    'locale' => $translation->locale,
                    'slug' => $translation->slug
                ]);
            } else {
                return route('cms.page.show', ['slug' => $translation->slug]);
            }
        } catch (\Exception $e) {
            // Route not available (e.g., in tests)
            return null;
        }
    }
}
