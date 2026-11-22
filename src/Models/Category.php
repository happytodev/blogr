<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Tests\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool|null $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, BlogPost> $posts
 * @property-read Collection<int, CategoryTranslation> $translations
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_default'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    public function getTable()
    {
        return config('blogr.tables.prefix', '').'categories';
    }

    /**
     * @return HasMany<BlogPost, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * A category has many translations
     */
    /**
     * @return HasMany<CategoryTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'category_id');
    }

    /**
     * Get the translation for a specific locale
     */
    public function translate(string $locale): ?CategoryTranslation
    {
        // If translations are already loaded, use them (avoid N+1)
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        // Otherwise, query the database
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get the default translation (English)
     */
    public function getDefaultTranslation(): ?CategoryTranslation
    {
        return $this->translate('en') ?? $this->translations()->first();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
