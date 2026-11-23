<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $blog_series_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property string|null $photo
 * @property string|null $slug
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read BlogSeries $series
 * @property int|null $author_bio_id
 */
class BlogSeriesTranslation extends Model
{
    use HasFactory;

    protected $table = 'blog_series_translations';

    protected $fillable = [
        'blog_series_id',
        'locale',
        'slug',
        'title',
        'description',
        'photo',
        'seo_title',
        'seo_description',
        'author_bio_id',
    ];

    /**
     * The bio this translation prefers for the posts it groups (#376).
     *
     * @return BelongsTo<AuthorBio, $this>
     */
    public function authorBio(): BelongsTo
    {
        return $this->belongsTo(AuthorBio::class, 'author_bio_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($translation) {
            if (empty($translation->slug) && ! empty($translation->title)) {
                $translation->slug = Str::slug($translation->title);
            }
        });

        // Update slug if title changes and slug is empty
        static::updating(function ($translation) {
            if (empty($translation->slug) && ! empty($translation->title)) {
                $translation->slug = Str::slug($translation->title);
            }
        });
    }

    /**
     * Get the series that owns this translation.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(BlogSeries::class, 'blog_series_id');
    }
}
