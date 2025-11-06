<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPageTranslation extends Model
{
    protected $fillable = [
        'cms_page_id',
        'locale',
        'slug',
        'title',
        'content',
        'blocks', // JSON blocks for page builder (per translation)
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'blocks' => 'array',
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
        $cmsPrefix = config('blogr.cms.route.prefix', '');
        $baseUrl = rtrim(config('app.url'), '/');
        
        // Build URL parts
        $parts = array_filter([
            $baseUrl,
            $cmsPrefix,
            $this->locale !== config('blogr.locales.default') ? $this->locale : null,
            $this->slug,
        ]);
        
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
        if (!$this->meta_keywords) {
            return [];
        }

        return array_map('trim', explode(',', $this->meta_keywords));
    }
}
