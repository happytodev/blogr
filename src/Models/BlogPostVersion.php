<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostVersion extends Model
{
    protected $table = 'blog_post_versions';

    protected $fillable = [
        'blog_post_translation_id',
        'version_number',
        'title', 'slug', 'content', 'tldr',
        'seo_title', 'seo_description', 'seo_keywords',
        'photo',
        'categories', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'tags' => 'array',
        ];
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(BlogPostTranslation::class, 'blog_post_translation_id');
    }
}
