<?php

namespace Happytodev\Blogr\Helpers;

use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Str;

class SEOHelper
{
    /**
     * Build a page title from the configured pattern (issue #366).
     *
     * Placeholders: {title}, {site}, {series}, {category}. A placeholder that has
     * no value leaves its separator behind, so the result is cleaned: a post with
     * no series reads "AF-01 — My Blog", never "AF-01 —  — My Blog".
     *
     * @param  array<string, string|null>  $context
     */
    public static function title(string $title, array $context = [], ?string $locale = null): string
    {
        $pattern = config('blogr.seo.title_pattern', '{title} — {site}');
        $site = ConfigHelper::getSeoSiteName($locale);

        if (! is_string($pattern) || trim($pattern) === '') {
            $pattern = '{title} — {site}';
        }

        $values = [
            '{title}' => trim($title),
            '{site}' => trim($site),
            '{series}' => trim((string) ($context['series'] ?? '')),
            '{category}' => trim((string) ($context['category'] ?? '')),
        ];

        // Walk the pattern instead of the resolved string: a separator only exists
        // in the pattern, and a title may legitimately contain one ("AF-01 · day 11").
        preg_match_all('/\{[a-z]+\}|[^{]+/u', $pattern, $matches);
        $tokens = $matches[0];

        $segments = [];
        $pendingLiteral = '';

        foreach ($tokens as $token) {
            if (isset($values[$token])) {
                if ($values[$token] !== '') {
                    if ($segments === [] && $pendingLiteral !== '') {
                        $segments[] = ['separator' => '', 'value' => trim($pendingLiteral)];
                        $pendingLiteral = '';
                    }

                    $segments[] = [
                        'separator' => $segments === [] ? '' : ($pendingLiteral !== '' ? $pendingLiteral : ' '),
                        'value' => $values[$token],
                    ];
                }

                // An empty placeholder takes its separator with it.
                $pendingLiteral = '';

                continue;
            }

            $pendingLiteral .= $token;
        }

        $resolved = '';
        foreach ($segments as $segment) {
            $resolved .= $segment['separator'].$segment['value'];
        }

        // "HTD — HTD": a site whose name is also its page title reads as a stutter.
        $seen = [];
        $unique = [];

        foreach ($segments as $segment) {
            if (in_array($segment['value'], $seen, true)) {
                continue;
            }

            $seen[] = $segment['value'];
            $unique[] = $segment;
        }

        $resolved = '';
        foreach ($unique as $index => $segment) {
            $resolved .= ($index === 0 ? '' : $segment['separator']).$segment['value'];
        }

        $resolved = trim((string) preg_replace('/\s{2,}/u', ' ', $resolved), " \t\n\r\0\x0B");

        if ($resolved === '' || $resolved === trim($title)) {
            return trim($title) !== '' ? trim($title) : $site;
        }

        return $resolved;
    }

    /**
     * Flatten markdown to one line of plain text (issue #367).
     */
    public static function plainText(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        try {
            $html = MarkdownHelper::toHtml($markdown);
        } catch (\Throwable) {
            $html = $markdown;
        }

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * The description a search engine gets: what the editor wrote, otherwise the
     * body flattened to text — never the markdown source (issue #367).
     */
    public static function description(?string $explicit, ?string $body = null): string
    {
        $explicit = $explicit === null ? '' : trim($explicit);

        if ($explicit !== '') {
            return Str::limit($explicit, 200);
        }

        $text = self::plainText($body);

        if ($text === '') {
            return ConfigHelper::getSeoDefaultDescription();
        }

        return Str::limit($text, 160);
    }

    /**
     * Generate SEO metadata for blog listing pages (index, category, tag)
     */
    public static function forListingPage(string $type = 'index', ?string $name = null, ?string $description = null): array
    {
        $currentLocale = app()->getLocale();

        $baseTitle = ConfigHelper::getSeoDefaultTitle($currentLocale);
        $baseDescription = $description ?: ConfigHelper::getSeoDefaultDescription($currentLocale);
        $baseKeywords = ConfigHelper::getSeoDefaultKeywords($currentLocale);

        $seo = [
            'title' => self::title($name !== null && $name !== '' ? $name : $baseTitle, [], $currentLocale),
            'description' => $baseDescription,
            'keywords' => $baseKeywords,
            'canonical' => url()->current(),
            'og_type' => 'website',
            'schema_type' => 'WebPage',
            'robots' => 'index, follow',
        ];

        switch ($type) {
            case 'category':
                if ($name) {
                    $seo['title'] = self::title($name, ['category' => $name], $currentLocale);
                    $seo['description'] = __('blogr::blogr.seo.category_description', ['name' => $name]);
                    $seo['keywords'] = "{$name}, {$baseKeywords}";
                }
                break;

            case 'tag':
                if ($name) {
                    $seo['title'] = self::title($name, [], $currentLocale);
                    $seo['description'] = __('blogr::blogr.seo.tag_description', ['name' => $name]);
                    $seo['keywords'] = "{$name}, {$baseKeywords}";
                }
                break;

            case 'index':
            default:
                // Keep default values
                break;
        }

        return $seo;
    }

    /**
     * Generate SEO metadata for individual blog post
     */
    public static function forBlogPost(BlogPost $post): array
    {
        $config = config('blogr.seo', []);
        $defaultConfig = [
            'og' => [
                'image' => '/images/blogr.webp',
                'image_width' => 1200,
                'image_height' => 630,
            ],
        ];

        // Use post-specific metadata if available, otherwise fall back to defaults
        $title = $post->meta_title ?: $post->title;
        $description = $post->meta_description ?: self::generateDescription($post->content);
        $keywords = $post->meta_keywords ?: self::generateKeywords($post->title, $post->content, $post->tags);

        $seo = [
            'title' => $title,
            'description' => Str::limit($description, 160),
            'keywords' => $keywords,
            'canonical' => PostUrlHelper::forPost($post),
            'og_type' => 'article',
            'schema_type' => 'BlogPosting',
            'robots' => 'index, follow',
            'author' => $post->user->name ?? ($config['site_name'] ?? 'My Blog'),
            'published_time' => $post->published_at?->toISOString(),
            'modified_time' => $post->updated_at?->toISOString(),
            'tags' => $post->tags->pluck('name')->toArray(),
        ];

        // Add image if post has one
        if ($post->photo) {
            $seo['image'] = $post->photo_url;
            $seo['image_width'] = 1200; // Default OG image dimensions
            $seo['image_height'] = 630;
        } else {
            $seo['image'] = asset($config['og']['image'] ?? $defaultConfig['og']['image']);
            $seo['image_width'] = $config['og']['image_width'] ?? $defaultConfig['og']['image_width'];
            $seo['image_height'] = $config['og']['image_height'] ?? $defaultConfig['og']['image_height'];
        }

        // Note: Structured data will be generated by generateJsonLd method

        return $seo;
    }

    /**
     * Generate description from post content
     */
    private static function generateDescription(string $content): string
    {
        // Strip HTML and get first 160 characters
        $plainText = strip_tags($content);
        $description = Str::limit($plainText, 160);

        // If description is too short, add default text
        if (strlen($description) < 50) {
            $description .= ' Read more on our blog.';
        }

        return $description;
    }

    /**
     * Generate keywords from title, content, and tags
     */
    private static function generateKeywords(string $title, string $content, $tags): string
    {
        $keywords = [];

        // Add words from title (important keywords)
        $titleWords = explode(' ', $title);
        $keywords = array_merge($keywords, array_slice($titleWords, 0, 3));

        // Add tag names
        if ($tags && $tags->count() > 0) {
            $keywords = array_merge($keywords, $tags->pluck('name')->toArray());
        }

        // Add some default keywords
        $keywords = array_merge($keywords, ['blog', 'article', 'news']);

        // Remove duplicates and limit to 10 keywords
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 10);

        return implode(', ', $keywords);
    }

    /**
     * Generate complete JSON-LD structured data for a page
     */
    /**
     * BreadcrumbList structured data, for a page that has a trail (issue #371).
     *
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public static function generateBreadcrumbJsonLd(array $items): string
    {
        $list = [];

        foreach (array_values($items) as $index => $item) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function generateJsonLd(array $seoData): string
    {
        $config = config('blogr.seo', []);
        $defaultConfig = [
            'structured_data' => [
                'organization' => [
                    'name' => 'My Blog',
                    'url' => url('/'),
                    'logo' => url('/images/logo.png'),
                ],
            ],
            'default_description' => 'Discover our latest articles and insights',
        ];

        $baseSchema = [
            '@context' => 'https://schema.org',
            '@type' => $seoData['schema_type'] ?? 'WebPage',
            'name' => $seoData['title'] ?? ($config['default_title'] ?? 'Blog'),
            'description' => $seoData['description'] ?? ($config['default_description'] ?? $defaultConfig['default_description']),
            'url' => $seoData['canonical'] ?? url()->current(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $config['structured_data']['organization']['name'] ?? $defaultConfig['structured_data']['organization']['name'],
                'url' => $config['structured_data']['organization']['url'] ?? $defaultConfig['structured_data']['organization']['url'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $config['structured_data']['organization']['logo'] ?? $defaultConfig['structured_data']['organization']['logo'],
                ],
            ],
        ];

        // Add additional schema data if available (for blog posts)
        if (isset($seoData['schema_additional'])) {
            $additionalData = json_decode($seoData['schema_additional'], true);
            if ($additionalData) {
                $baseSchema = array_merge($baseSchema, $additionalData);
            }
        }

        // Add specific properties for blog posts
        if (($seoData['schema_type'] ?? '') === 'BlogPosting') {
            $baseSchema['@type'] = 'BlogPosting';
            $baseSchema['headline'] = $seoData['title'] ?? $config['default_title'];
            if (isset($seoData['author'])) {
                $baseSchema['author'] = [
                    '@type' => 'Person',
                    'name' => $seoData['author'],
                ];
            }
            if (isset($seoData['published_time'])) {
                $baseSchema['datePublished'] = $seoData['published_time'];
            }
            if (isset($seoData['modified_time'])) {
                $baseSchema['dateModified'] = $seoData['modified_time'];
            }
            if (isset($seoData['image'])) {
                $baseSchema['image'] = $seoData['image'];
            }
        }

        return json_encode($baseSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS);
    }
}
