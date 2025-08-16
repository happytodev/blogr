<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'photo',
        'content',
        'slug',
        'user_id',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'tldr'
    ];

    public function getTable()
    {
        return config('blogr.tables.prefix', '') . 'blog_posts';
    }

    // A blog post belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
