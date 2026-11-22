<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Tests\Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, BlogPost> $posts
 * @property-read Collection<int, TagTranslation> $translations
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TagFactory::new();
    }

    public function getTable()
    {
        return config('blogr.tables.prefix', '').'tags';
    }

    /**
     * @return BelongsToMany<BlogPost, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, config('blogr.tables.prefix', '').'blog_post_tag');
    }

    /**
     * @return HasMany<TagTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class, 'tag_id');
    }

    public function translate(string $locale): ?TagTranslation
    {
        // If translations are already loaded, use them (avoid N+1)
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        // Otherwise, query the database
        return $this->translations()->where('locale', $locale)->first();
    }

    public function getDefaultTranslation(): ?TagTranslation
    {
        return $this->translate('en') ?? $this->translations()->first();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = static::generateUniqueSlug($tag->name);
            }
        });

        static::updating(function ($tag) {
            // Only regenerate slug if name has changed and slug hasn't been explicitly set to a different value
            if ($tag->isDirty('name')) {
                $expectedSlug = static::generateUniqueSlug($tag->name, $tag->id);
                // Only regenerate if the current slug matches what would be auto-generated from the old name
                $oldSlug = static::generateUniqueSlug($tag->getOriginal('name'), $tag->id);
                if ($tag->slug === $oldSlug) {
                    $tag->slug = $expectedSlug;
                }
                // If slug was explicitly set to something else, preserve it
            }
        });
    }

    protected static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
