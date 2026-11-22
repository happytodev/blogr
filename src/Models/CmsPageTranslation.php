<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Traits\ClearsLocaleCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cms_page_id
 * @property string $locale
 * @property string $slug
 * @property string $title
 * @property string|null $content
 * @property string|null $excerpt
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CmsPage|null $page
 */
class CmsPageTranslation extends Model
{
    use ClearsLocaleCache;

    protected $fillable = [
        'cms_page_id',
        'locale',
        'slug',
        'title',
        'content',
        'blocks',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_complete',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_complete' => 'boolean',
    ];

    /**
     * Get the parent CMS page
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }

    /**
     * Get the full URL for this translation
     */
    public function url(): string
    {
        $configuredPrefix = config('blogr.cms.prefix');
        $cmsPrefix = is_string($configuredPrefix) ? trim($configuredPrefix, '/') : '';

        $configuredUrl = config('app.url');
        $baseUrl = is_string($configuredUrl) ? rtrim($configuredUrl, '/') : '';

        $defaultLocale = config('blogr.locales.default');

        // Build URL parts
        $parts = array_filter([
            $baseUrl,
            $cmsPrefix,
            $this->locale !== $defaultLocale ? $this->locale : null,
            $this->slug,
        ], fn ($part) => is_string($part) && $part !== '');

        return implode('/', $parts);
    }

    /**
     * Get SEO title (meta_title or fallback to title)
     */
    public function seoTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /**
     * Get SEO description
     */
    public function seoDescription(): ?string
    {
        return $this->meta_description;
    }

    /**
     * Get SEO keywords as array
     */
    public function seoKeywords(): array
    {
        if (! $this->meta_keywords) {
            return [];
        }

        return array_map('trim', explode(',', $this->meta_keywords));
    }
}
