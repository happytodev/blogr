<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function getTable()
    {
        return config('blogr.tables.prefix', '') . 'tags';
    }

    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, config('blogr.tables.prefix', '') . 'blog_post_tag');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}