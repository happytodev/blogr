<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\Category;
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
        'tldr',
        'category_id',
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

    // A blog post belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // A blog post can have many tags
    // Many-to-many relationship with Tag model
    public function tags()
    {
        return $this->belongsToMany(Tag::class, config('blogr.tables.prefix', '') . 'blog_post_tag');
    }
}
