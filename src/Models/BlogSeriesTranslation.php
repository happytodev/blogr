<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogSeriesTranslation extends Model
{
    use HasFactory;

    protected $table = 'blog_series_translations';

    protected $fillable = [
        'blog_series_id',
        'locale',
        'title',
        'description',
        'seo_title',
        'seo_description',
    ];

    /**
     * Get the series that owns this translation.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(BlogSeries::class, 'blog_series_id');
    }
}
