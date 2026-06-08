<?php

namespace Happytodev\Blogr\Http\Controllers;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Http\Response;

class SitemapController
{
    public function index(?string $locale = null): Response
    {
        $locale = $locale ?? config('blogr.locales.default', 'en');
        $xml = $this->generateSitemap($locale);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    protected function generateSitemap(string $locale): string
    {
        $urls = [];

        // Blog posts
        $posts = BlogPost::with('translations')
            ->published()
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($posts as $post) {
            $translation = $post->translations->firstWhere('locale', $locale);
            if (! $translation) {
                $translation = $post->getDefaultTranslation();
            }
            if ($translation && $translation->slug) {
                $urls[] = [
                    'loc' => route('blog.show', ['locale' => $locale, 'slug' => $translation->slug]),
                    'lastmod' => $post->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        }

        // Categories
        $categories = Category::all();
        foreach ($categories as $category) {
            $slug = $category->slug;
            $urls[] = [
                'loc' => route('blog.category', ['locale' => $locale, 'categorySlug' => $slug]),
                'lastmod' => $category->updated_at->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        // Tags
        $tags = Tag::all();
        foreach ($tags as $tag) {
            $slug = $tag->slug;
            $urls[] = [
                'loc' => route('blog.tag', ['locale' => $locale, 'tagSlug' => $slug]),
                'lastmod' => $tag->updated_at->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        // Series
        if (config('blogr.series.enabled', true)) {
            $series = BlogSeries::published()->get();
            foreach ($series as $s) {
                $translation = $s->translate($locale);
                $slug = $translation?->slug ?? $s->slug;
                $urls[] = [
                    'loc' => route('blog.series', ['locale' => $locale, 'seriesSlug' => $slug]),
                    'lastmod' => $s->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];
            }
        }

        // CMS pages
        if (config('blogr.cms.enabled', true)) {
            $pages = CmsPage::with('translations')
                ->where('is_published', true)
                ->get();
            foreach ($pages as $page) {
                $translation = $page->translations->firstWhere('locale', $locale);
                if (! $translation) {
                    $translation = $page->getDefaultTranslation();
                }
                if ($translation && $translation->slug) {
                    if ($page->is_homepage) {
                        $url = $locale ? url("/{$locale}") : url('/');
                    } else {
                        $prefix = trim(config('blogr.cms.route.prefix', ''), '/');
                        $path = $prefix ? "{$locale}/{$prefix}/{$translation->slug}" : "{$locale}/{$translation->slug}";
                        $url = $locale ? url("/{$path}") : url("/{$translation->slug}");
                    }
                    $urls[] = [
                        'loc' => $url,
                        'lastmod' => $page->updated_at->toW3cString(),
                        'changefreq' => $page->is_homepage ? 'daily' : 'monthly',
                        'priority' => $page->is_homepage ? '1.0' : '0.7',
                    ];
                }
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.$this->escapeXml($url['loc']).'</loc>'."\n";
            $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    protected function escapeXml(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
