<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Support\Str;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'is_default'];

    public function getTable()
    {
        return config('blogr.tables.prefix', '') . 'categories';
    }

    public function posts()
    {
        return $this->hasMany(BlogPost::class);
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