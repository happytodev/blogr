<?php

namespace Happytodev\Blogr\Http\Controllers;

use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Helpers\SEOHelper;
use Happytodev\Blogr\Helpers\ConfigHelper;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

class BlogController
{
    public function index($locale = null)
    {
        // Handle locale
        $locale = $this->resolveLocale($locale);
        
        // Get posts that have translations in this locale with pagination
        $posts = BlogPost::whereHas('translations', function($query) use ($locale) {
                $query->where('locale', $locale);
            })
            ->with([
                'category.translations', 
                'tags.translations', 
                'translations' => function($query) use ($locale) {
                    $query->where('locale', $locale);
                }
            ])
            ->latest()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->paginate(config('blogr.posts_per_page', 10))
            ->through(function ($post) use ($locale) {
                // Get the translation for this locale
                $translation = $post->translations->first();
                
                // Override post attributes with translation
                if ($translation) {
                    $post->translated_title = $translation->title;
                    $post->translated_slug = $translation->slug;
                    $post->translated_excerpt = $translation->excerpt;
                    $post->translated_tldr = $translation->tldr;
                }
                
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl(
                        $post->photo,
                        now()->addHours(1) // URL valid for 1 hour
                    );
                }
                return $post;
            });

        // Get featured series with their translations
        $featuredSeries = BlogSeries::where('is_featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['translations' => function($query) use ($locale) {
                $query->where('locale', $locale);
            }, 'posts' => function($query) {
                $query->where('is_published', true)
                      ->orderBy('series_position');
            }])
            ->orderBy('position')
            ->take(3)
            ->get()
            ->map(function ($series) use ($locale) {
                $translation = $series->translations->first();
                if ($translation) {
                    $series->translated_title = $translation->title;
                    $series->translated_description = $translation->description;
                }
                if ($series->photo) {
                    $series->photo_url = Storage::temporaryUrl(
                        $series->photo,
                        now()->addHours(1)
                    );
                }
                return $series;
            });

        $seoData = SEOHelper::forListingPage('index');

        return View::make('blogr::blog.index', [
            'posts' => $posts,
            'featuredSeries' => $featuredSeries,
            'seoData' => $seoData,
            'currentLocale' => $locale,
            'availableLocales' => config('blogr.locales.available', ['en']),
        ]);
    }

    public function show($localeOrSlug, $slug = null)
    {
        // Determine if locales are enabled
        $localesEnabled = config('blogr.locales.enabled', false);
        
        // Parse parameters
        if ($localesEnabled && $slug !== null) {
            // Format: /{locale}/blog/{slug}
            $locale = $localeOrSlug;
            $actualSlug = $slug;
        } else {
            // Format: /blog/{slug} (backward compatibility)
            $locale = config('blogr.locales.default', 'en');
            $actualSlug = $localeOrSlug;
        }
        
        // Validate locale
        $locale = $this->resolveLocale($locale);
        
        // Fetch translation first
        $translation = BlogPostTranslation::where('slug', $actualSlug)
            ->where('locale', $locale)
            ->with([
                'post.category.translations', 
                'post.tags.translations', 
                'post.translations',
                'post.series.translations',
                'post.series.posts.translations'
            ])
            ->first(); // Use first() instead of firstOrFail()
        
        if ($translation) {
            $post = $translation->post;
        } else {
            // If no translation found, try to find post by slug directly (for non-localized posts)
            $post = BlogPost::where('slug', $actualSlug)
                ->with([
                    'category.translations', 
                    'tags.translations', 
                    'translations',
                    'series.translations',
                    'series.posts.translations'
                ])
                ->first();
                
            if (!$post) {
                abort(404);
            }
        }
        
        // Check if post is published
        if (!$post->is_published) {
            abort(404);
        }
        
        if ($post->published_at && $post->published_at->isFuture()) {
            abort(404);
        }
        
        // Prepare markdown converter
        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'before',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'title' => 'Permalink',
                'symbol' => '#',
                'aria_hidden' => true,
            ],
            'table_of_contents' => [
                'position' => 'placeholder',
                'placeholder' => '[[TOC]]',
                'style' => 'bullet',
                'min_heading_level' => 2,
                'max_heading_level' => 6,
                'normalize' => 'relative',
                'html_class' => 'toc',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        $converter = new MarkdownConverter($environment);

        // Get the best available translation
        if (!$translation) {
            $translation = $post->getDefaultTranslation();
        }
        
        // If still no translation, show 404
        if (!$translation) {
            abort(404);
        }

        // Get content without frontmatter from translation
        $contentWithoutFrontmatter = $this->getContentWithoutFrontmatter($translation->content);

        // Only add TOC if it should be displayed
        if ($post->shouldDisplayToc()) {
            $tocTitle = __('blogr::blogr.ui.table_of_contents');
            $markdownWithToc = "# {$tocTitle}\n\n[[TOC]]\n\n" . $contentWithoutFrontmatter;
            $convertedContent = $converter->convert($markdownWithToc)->getContent();
        } else {
            $convertedContent = $converter->convert($contentWithoutFrontmatter)->getContent();
        }

        // Set converted content on post for backward compatibility with views
        $post->setAttribute('content', $convertedContent);
        
        // Check if current locale translation is available
        $translationAvailable = $post->translations->contains('locale', $locale);

        // Prepare display data from translation
        $displayData = [
            'title' => $translation->title,
            'slug' => $translation->slug,
            'content' => $convertedContent,
            'excerpt' => $translation->excerpt,
            'tldr' => $translation->tldr,
            'seo_title' => $translation->seo_title,
            'seo_description' => $translation->seo_description,
            'seo_keywords' => $translation->seo_keywords,
            'translationAvailable' => $translationAvailable,
            'currentTranslationLocale' => $translation->locale,
            'reading_time' => $translation->reading_time ?? $post->getEstimatedReadingTime(),
        ];
        
        // Add photo URL if exists
        if ($post->photo) {
            $post->photo_url = Storage::temporaryUrl(
                $post->photo,
                now()->addHours(1)
            );
        }
        
        // Get available translations for language switcher
        $availableTranslations = $post->translations->map(function ($trans) use ($localesEnabled) {
            return [
                'locale' => $trans->locale,
                'title' => $trans->title,
                'url' => $localesEnabled 
                    ? route('blog.show', ['locale' => $trans->locale, 'slug' => $trans->slug])
                    : route('blog.show', ['slug' => $trans->slug]),
            ];
        });

        // Build SEO data using translation data instead of post data
        // Use content without frontmatter for description
        $contentWithoutFrontmatter = $this->getContentWithoutFrontmatter($translation->content);
        $seoData = [
            'title' => $translation->seo_title ?: $translation->title,
            'description' => $translation->seo_description ?: Str::limit(strip_tags($contentWithoutFrontmatter), 160),
            'keywords' => $translation->seo_keywords ?: $translation->title,
            'canonical' => $localesEnabled 
                ? route('blog.show', ['locale' => $locale, 'slug' => $translation->slug])
                : route('blog.show', ['slug' => $translation->slug]),
            'og_type' => 'article',
            'schema_type' => 'BlogPosting',
            'site_name' => ConfigHelper::getSeoSiteName($locale),
            'robots' => 'index, follow',
            'author' => $post->user->name ?? ConfigHelper::getSeoSiteName($locale),
            'published_time' => $post->published_at?->toISOString(),
            'modified_time' => $post->updated_at->toISOString(),
            'tags' => $post->tags->pluck('name')->toArray(),
        ];

        // Add image if post has one
        if ($post->photo) {
            $seoData['image'] = $post->photo_url;
            $seoData['image_width'] = 1200;
            $seoData['image_height'] = 630;
        } else {
            $seoData['image'] = asset(config('blogr.seo.og.image', '/images/blogr.webp'));
            $seoData['image_width'] = config('blogr.seo.og.image_width', 1200);
            $seoData['image_height'] = config('blogr.seo.og.image_height', 630);
        }

        // Add structured data for JSON-LD
        $seoData['schema_additional'] = json_encode([
            'headline' => $translation->title,
            'author' => [
                '@type' => 'Person',
                'name' => $post->user->name ?? ConfigHelper::getSeoSiteName($locale),
            ],
            'datePublished' => $post->published_at?->toISOString(),
            'dateModified' => $post->updated_at->toISOString(),
        ]);

        // Prepare translated slugs for series posts if post is part of a series
        if ($post->series) {
            // Translate the series itself
            $seriesTranslation = $post->series->translations->firstWhere('locale', $locale);
            if ($seriesTranslation) {
                $post->series->translated_title = $seriesTranslation->title;
                $post->series->translated_description = $seriesTranslation->description;
            }
            
            // Translate each post in the series
            $post->series->posts->each(function ($seriesPost) use ($locale) {
                $seriesTranslation = $seriesPost->translations->firstWhere('locale', $locale);
                if ($seriesTranslation) {
                    $seriesPost->translated_slug = $seriesTranslation->slug;
                    $seriesPost->translated_title = $seriesTranslation->title;
                }
            });
        }

        return View::make('blogr::blog.show', [
            'post' => $post,
            'displayData' => $displayData,
            'currentLocale' => $locale,
            'availableTranslations' => $availableTranslations,
            'seoData' => $seoData
        ]);
    }

    public function category($localeOrCategorySlug, $categorySlug = null)
    {
        // Determine if locales are enabled
        $localesEnabled = config('blogr.locales.enabled', false);
        
        // Parse parameters
        if ($localesEnabled && $categorySlug !== null) {
            // Format: /{locale}/blog/category/{categorySlug}
            $locale = $localeOrCategorySlug;
            $actualCategorySlug = $categorySlug;
        } else {
            // Format: /blog/category/{categorySlug} (backward compatibility)
            $locale = config('blogr.locales.default', 'en');
            $actualCategorySlug = $localeOrCategorySlug;
        }
        
        // Validate and resolve locale
        $currentLocale = $this->resolveLocale($locale);
        
        // Try to find category by main slug first
        $category = Category::where('slug', $actualCategorySlug)->first();
        
        // If not found, try to find by translated slug
        if (!$category) {
            $translation = \Happytodev\Blogr\Models\CategoryTranslation::where('slug', $actualCategorySlug)
                ->where('locale', $currentLocale)
                ->first();
                
            if ($translation) {
                $category = $translation->category;
            }
        }
        
        // If still not found, 404
        if (!$category) {
            abort(404);
        }
        
        // Get the translation for current locale
        $categoryTranslation = $category->translate($currentLocale);
        $displayName = $categoryTranslation ? $categoryTranslation->name : $category->name;
        
        $posts = BlogPost::with([
                'category.translations', 
                'tags.translations',
                'translations' => function($query) use ($currentLocale) {
                    $query->where('locale', $currentLocale);
                }
            ])
            ->where('category_id', $category->id)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->latest()
            ->paginate(config('blogr.posts_per_page', 10))
            ->through(function ($post) {
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl($post->photo, now()->addMinutes(5));
                }
                return $post;
            });

        return View::make('blogr::blog.category', [
            'category' => $category,
            'categoryTranslation' => $categoryTranslation,
            'displayName' => $displayName,
            'posts' => $posts,
            'currentLocale' => $currentLocale,
            'seoData' => SEOHelper::forListingPage('category', $displayName)
        ]);
    }

    public function tag($localeOrTagSlug, $tagSlug = null)
    {
        // Determine if locales are enabled
        $localesEnabled = config('blogr.locales.enabled', false);
        
        // Parse parameters
        if ($localesEnabled && $tagSlug !== null) {
            // Format: /{locale}/blog/tag/{tagSlug}
            $locale = $localeOrTagSlug;
            $actualTagSlug = $tagSlug;
        } else {
            // Format: /blog/tag/{tagSlug} (backward compatibility)
            $locale = config('blogr.locales.default', 'en');
            $actualTagSlug = $localeOrTagSlug;
        }
        
        // Validate and resolve locale
        $currentLocale = $this->resolveLocale($locale);
        
        // Try to find tag by main slug first
        $tag = Tag::where('slug', $actualTagSlug)->first();
        
        // If not found, try to find by translated slug
        if (!$tag) {
            $translation = \Happytodev\Blogr\Models\TagTranslation::where('slug', $actualTagSlug)
                ->where('locale', $currentLocale)
                ->first();
                
            if ($translation) {
                $tag = $translation->tag;
            }
        }
        
        // If still not found, 404
        if (!$tag) {
            abort(404);
        }
        
        // Get the translation for current locale
        $tagTranslation = $tag->translate($currentLocale);
        $displayName = $tagTranslation ? $tagTranslation->name : $tag->name;
        
        $posts = $tag->posts()
            ->with([
                'category.translations', 
                'tags.translations',
                'translations' => function($query) use ($currentLocale) {
                    $query->where('locale', $currentLocale);
                }
            ])
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->latest()
            ->take(config('blogr.posts_per_page', 10))
            ->get()
            ->map(function ($post) {
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl($post->photo, now()->addMinutes(5));
                }
                return $post;
            });

        return View::make('blogr::blog.tag', [
            'tag' => $tag,
            'tagTranslation' => $tagTranslation,
            'displayName' => $displayName,
            'posts' => $posts,
            'currentLocale' => $currentLocale,
            'seoData' => SEOHelper::forListingPage('tag', $displayName)
        ]);
    }

    public function seriesIndex($locale = null)
    {
        // Handle locale
        $locale = $this->resolveLocale($locale);
        
        $series = \Happytodev\Blogr\Models\BlogSeries::with(['translations', 'posts'])
            ->published()
            ->orderBy('position')
            ->get()
            ->map(function ($s) use ($locale) {
                $translation = $s->translate($locale) ?? $s->getDefaultTranslation();
                $s->title = $translation?->title ?? $s->slug;
                $s->description = $translation?->description ?? '';
                return $s;
            });
        
        $seoData = [
            'title' => 'Blog Series - ' . config('app.name'),
            'description' => 'Browse all our blog series and learn step by step.',
            'canonical' => config('blogr.locales.enabled') 
                ? route('blog.series.index', ['locale' => $locale])
                : route('blog.series.index'),
        ];

        return View::make('blogr::blog.series-index', [
            'series' => $series,
            'currentLocale' => $locale,
            'seoData' => $seoData
        ]);
    }

    public function series($localeOrSlug, $seriesSlug = null)
    {
        // Determine if locales are enabled
        $localesEnabled = config('blogr.locales.enabled', false);
        
        // Parse parameters
        if ($localesEnabled && $seriesSlug !== null) {
            // Format: /{locale}/blog/series/{seriesSlug}
            $locale = $localeOrSlug;
            $actualSlug = $seriesSlug;
        } else {
            // Format: /blog/series/{seriesSlug} (backward compatibility)
            $locale = config('blogr.locales.default', 'en');
            $actualSlug = $localeOrSlug;
        }
        
        // Validate locale
        $locale = $this->resolveLocale($locale);
        
        $series = \Happytodev\Blogr\Models\BlogSeries::where('slug', $actualSlug)
            ->published()
            ->firstOrFail();
        
        $posts = $series->posts()
            ->with(['translations'])
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->orderBy('series_position')
            ->orderBy('position')
            ->get()
            ->map(function ($post) use ($locale) {
                // Add translated slug for each post
                $translation = $post->translations->firstWhere('locale', $locale);
                if ($translation) {
                    $post->translated_slug = $translation->slug;
                    $post->translated_title = $translation->title;
                    $post->translated_excerpt = $translation->excerpt;
                }
                
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl(
                        $post->photo,
                        now()->addHours(1)
                    );
                }
                return $post;
            });

        $seriesTranslation = $series->translate($locale) ?? $series->getDefaultTranslation();
        
        $seoData = [
            'title' => $seriesTranslation?->seo_title ?? $seriesTranslation?->title ?? $series->slug,
            'description' => $seriesTranslation?->seo_description ?? $seriesTranslation?->description ?? '',
            'canonical' => $localesEnabled 
                ? route('blog.series', ['locale' => $locale, 'seriesSlug' => $actualSlug])
                : route('blog.series', ['seriesSlug' => $actualSlug]),
        ];

        return View::make('blogr::blog.series', [
            'series' => $series,
            'seriesTranslation' => $seriesTranslation,
            'posts' => $posts,
            'currentLocale' => $locale,
            'seoData' => $seoData
        ]);
    }

    /**
     * Resolve the locale from the request or use the default.
     *
     * @param string|null $locale
     * @return string
     */
    protected function resolveLocale($locale = null): string
    {
        $localesEnabled = config('blogr.locales.enabled', false);
        
        if (!$localesEnabled) {
            return config('blogr.locales.default', 'en');
        }
        
        if ($locale && in_array($locale, config('blogr.locales.available', ['en']))) {
            return $locale;
        }
        
        return config('blogr.locales.default', 'en');
    }

    /**
     * Get content without frontmatter for markdown conversion.
     *
     * @param string $content
     * @return string
     */
    protected function getContentWithoutFrontmatter(string $content): string
    {
        // Remove frontmatter (content between --- and ---)
        $pattern = '/^---\s*\n.*?\n---\s*\n/s';
        return preg_replace($pattern, '', $content);
    }
}
