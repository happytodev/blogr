<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostDraft extends Model
{
    protected $table = 'blog_post_drafts';

    protected $fillable = [
        'blog_post_translation_id',
        'blog_post_id',
        'draft_data',
    ];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
        ];
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(BlogPostTranslation::class, 'blog_post_translation_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
