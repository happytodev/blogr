<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Database\Factories\BlogSeriesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BlogSeries extends Model
{
    use HasFactory;

    protected $table = 'blog_series';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): BlogSeriesFactory
    {
        return BlogSeriesFactory::new();
    }

    protected $fillable = [
        'slug',
        'photo',
        'position',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get all translations for this series.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(BlogSeriesTranslation::class);
    }

    /**
     * Get all posts in this series.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class)->orderBy('series_position');
    }

    /**
     * Get unique authors who contributed to this series.
     * Authors are ordered by the number of posts they wrote (descending).
     * Returns a simple array (not a collection) for easier caching.
     *
     * @return array Array of User objects with posts_count attribute
     */
    public function authors(): array
    {
        // Get all posts with their authors
        $posts = $this->posts()->with('user')->get();
        
        // Group by user and count posts
        $authorsWithCounts = $posts->groupBy('user_id')
            ->map(function ($userPosts) {
                $user = $userPosts->first()->user;
                if ($user) {
                    $user->posts_count = $userPosts->count();
                }
                return $user;
            })
            ->filter() // Remove null users
            ->sortByDesc('posts_count') // Sort by post count
            ->values()
            ->toArray();
        
        return $authorsWithCounts;
    }

    /**
     * Get the translation for a specific locale.
     */
    public function translate(string $locale): ?BlogSeriesTranslation
    {
        // Use loaded translations if available, otherwise query
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }
        
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get the default translation (English).
     */
    public function getDefaultTranslation(): ?BlogSeriesTranslation
    {
        $enTranslation = $this->translate('en');
        
        if ($enTranslation) {
            return $enTranslation;
        }
        
        // Use loaded translations if available
        if ($this->relationLoaded('translations')) {
            return $this->translations->first();
        }
        
        return $this->translations()->first();
    }

    /**
     * Get the translated slug for a specific locale.
     * Falls back to base slug if no translation exists or if translation slug is empty.
     */
    public function getTranslatedSlug(string $locale): string
    {
        $translation = $this->translate($locale);
        
        // Return translated slug only if it exists and is not empty
        if ($translation && !empty($translation->slug)) {
            return $translation->slug;
        }
        
        return $this->slug;
    }

    /**
     * Get the title attribute from translation.
     */
    public function getTitleAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->title;
    }

    /**
     * Get the description attribute from translation.
     */
    public function getDescriptionAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->description;
    }

    /**
     * Get the photo URL attribute.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            // Use the 'public' disk for series images
            $disk = Storage::disk('public');
            
            try {
                // Try to generate temporary URL (works for S3, etc.)
                return $disk->temporaryUrl(
                    $this->photo,
                    now()->addHours(1)
                );
            } catch (\RuntimeException $e) {
                // Fallback to regular URL for local driver
                return $disk->url($this->photo);
            }
        }
        
        // Return default series image from config
        $defaultImage = config('blogr.series.default_image', '/images/default-series.svg');
        return asset($defaultImage);
    }

    /**
     * Scope a query to only include published series.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured series.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Check if the series is published.
     * Returns true if published_at is null (always published) or if the date is in the past.
     */
    public function isPublished(): bool
    {
        // If published_at is null, the series is always published
        if ($this->published_at === null) {
            return true;
        }
        
        // Otherwise, check if the published_at date is in the past
        return $this->published_at->isPast();
    }

    /**
     * Check if the series is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }
}
