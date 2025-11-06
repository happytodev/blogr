<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Enums\CmsPageTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'blocks',
        'template',
        'is_published',
        'is_homepage',
        'published_at',
        'default_locale',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_homepage' => 'boolean',
        'published_at' => 'datetime',
        'template' => CmsPageTemplate::class,
        'blocks' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Validate slug is not reserved before saving
        static::saving(function ($page) {
            $reservedSlugs = config('blogr.cms.reserved_slugs', []);
            
            if (in_array($page->slug, $reservedSlugs)) {
                throw new \InvalidArgumentException(
                    "The slug '{$page->slug}' is reserved and cannot be used for CMS pages. " .
                    "Reserved slugs: " . implode(', ', $reservedSlugs)
                );
            }

            // If setting as homepage, unset other homepages
            if ($page->is_homepage) {
                static::where('is_homepage', true)
                    ->where('id', '!=', $page->id)
                    ->update(['is_homepage' => false]);
            }
        });
    }

    /**
     * Get all translations for this page
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CmsPageTranslation::class);
    }

    /**
     * Get translation for specific locale
     */
    public function translation(string $locale): ?CmsPageTranslation
    {
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get translation for current locale or fallback to default
     */
    public function currentTranslation(?string $locale = null): ?CmsPageTranslation
    {
        $locale = $locale ?? app()->getLocale();
        
        return $this->translation($locale) 
            ?? $this->translation($this->default_locale)
            ?? $this->translations()->first();
    }

    /**
     * Check if page is published
     */
    public function isPublished(): bool
    {
        return $this->is_published 
            && $this->published_at 
            && $this->published_at->isPast();
    }

    /**
     * Get available locales for this page
     */
    public function availableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    /**
     * Scope: Only published pages
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: Homepage page
     */
    public function scopeHomepage($query)
    {
        return $query->where('is_homepage', true);
    }

    /**
     * Scope: By template type
     */
    public function scopeByTemplate($query, CmsPageTemplate|string $template)
    {
        $templateValue = $template instanceof CmsPageTemplate 
            ? $template->value 
            : $template;
            
        return $query->where('template', $templateValue);
    }

    /**
     * Create factory for the model
     */
    protected static function newFactory()
    {
        return \Happytodev\Blogr\Database\Factories\CmsPageFactory::new();
    }
}
