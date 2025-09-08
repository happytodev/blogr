<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;
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
                $tag->slug = static::generateUniqueSlug($tag->name);
            }
        });

        static::updating(function ($tag) {
            // Only regenerate slug if name has changed and slug hasn't been explicitly set to a different value
            if ($tag->isDirty('name')) {
                $expectedSlug = static::generateUniqueSlug($tag->name, $tag->id);
                // Only regenerate if the current slug matches what would be auto-generated from the old name
                $oldSlug = static::generateUniqueSlug($tag->getOriginal('name'), $tag->id);
                if ($tag->slug === $oldSlug) {
                    $tag->slug = $expectedSlug;
                }
                // If slug was explicitly set to something else, preserve it
            }
        });
    }

    protected static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}