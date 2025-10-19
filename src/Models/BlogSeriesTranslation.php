<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($translation) {
            if (empty($translation->slug) && !empty($translation->title)) {
                $translation->slug = Str::slug($translation->title);
            }
        });

        // Update slug if title changes and slug is empty
        static::updating(function ($translation) {
            if (empty($translation->slug) && !empty($translation->title)) {
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
