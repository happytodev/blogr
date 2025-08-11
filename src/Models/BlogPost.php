<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = ['title', 'photo', 'content', 'slug'];

    public function getTable()
    {
        return config('blogr.tables.prefix', '') . 'blog_posts';
    }
}