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
            // Fallback: construct URL manually using config
            $localesEnabled = config('blogr.locales.enabled', false);
            $defaultLocale = config('blogr.locales.default', 'en');
            $prefix = config('blogr.route.prefix', 'blog');
            $isHomepage = config('blogr.route.homepage', false);
            
            if ($localesEnabled) {
                if ($isHomepage || empty($prefix) || $prefix === '/') {
                    return "/{$defaultLocale}";
                }
                return "/{$defaultLocale}/{$prefix}";
            } else {
                if ($isHomepage || empty($prefix) || $prefix === '/') {
                    return "/";
                }
                return "/{$prefix}";
            }
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
            // Fallback: construct URL manually using config
            $translation = $category->translations()->first();
            if (!$translation) {
                return null;
            }
            
            $localesEnabled = config('blogr.locales.enabled', false);
            $defaultLocale = config('blogr.locales.default', 'en');
            $prefix = config('blogr.route.prefix', 'blog');
            $isHomepage = config('blogr.route.homepage', false);
            
            if ($localesEnabled) {
                if ($isHomepage || empty($prefix) || $prefix === '/') {
                    return "/{$defaultLocale}/category/{$translation->slug}";
                }
                return "/{$defaultLocale}/{$prefix}/category/{$translation->slug}";
            } else {
                if ($isHomepage || empty($prefix) || $prefix === '/') {
                    return "/category/{$translation->slug}";
                }
                return "/{$prefix}/category/{$translation->slug}";
            }
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
            // Fallback: construct URL manually using config
            $translation = $page->translations()->first();
            if (!$translation) {
                return null;
            }
            
            $localesEnabled = config('blogr.locales.enabled', false);
            $prefix = config('blogr.cms.prefix', 'page');
            
            // Check if this page is marked as homepage
            if ($page->is_homepage ?? false) {
                if ($localesEnabled) {
                    return "/{$translation->locale}";
                }
                return "/";
            }
            
            // Regular CMS page with prefix
            if ($localesEnabled) {
                if (empty($prefix)) {
                    return "/{$translation->locale}/{$translation->slug}";
                }
                return "/{$translation->locale}/{$prefix}/{$translation->slug}";
            } else {
                if (empty($prefix)) {
                    return "/{$translation->slug}";
                }
                return "/{$prefix}/{$translation->slug}";
            }
        }
    }
}
