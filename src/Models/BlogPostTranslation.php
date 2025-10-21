<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogPostTranslation extends Model
{
    use HasFactory;

    protected $table = 'blog_post_translations';

    protected $fillable = [
        'blog_post_id',
        'locale',
        'title',
        'slug',
        'photo',
        'content',
        'tldr',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'reading_time',
    ];

    /**
     * Get the post that owns this translation.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    /**
     * Get the categories associated with this translation.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'blog_post_translation_category',
            'blog_post_translation_id',
            'category_id'
        )->withTimestamps();
    }

    /**
     * Get the tags associated with this translation.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'blog_post_translation_tag',
            'blog_post_translation_id',
            'tag_id'
        )->withTimestamps();
    }

    /**
     * Calculate and store the reading time for this translation.
     */
    public function calculateReadingTime(): void
    {
        $readingSpeed = config('blogr.reading_speed.words_per_minute', 200);
        
        // Combine title and content for word count
        $text = $this->title . ' ' . $this->content;
        
        // Remove HTML tags and count words
        $text = strip_tags($text);
        $wordCount = str_word_count($text);
        
        // Calculate reading time in minutes
        $readingTime = ceil($wordCount / $readingSpeed);
        
        $this->reading_time = $readingTime;
        $this->save();
    }
}
