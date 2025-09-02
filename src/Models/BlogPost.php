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

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('blogr.tables.prefix', '') . 'blog_posts';
    }

    // Check if the post is scheduled for future publication
    public function isScheduled()
    {
        return $this->is_published && $this->published_at && $this->published_at->isFuture();
    }

    // Check if the post is currently published (either immediate or scheduled time reached)
    public function isCurrentlyPublished()
    {
        return $this->is_published && (!$this->published_at || $this->published_at->isPast());
    }

    // Get the publication status text
    public function getPublicationStatus()
    {
        if (!$this->is_published) {
            return 'draft';
        }

        if ($this->isScheduled()) {
            return 'scheduled';
        }

        return 'published';
    }

    // Get the publication status color
    public function getPublicationStatusColor()
    {
        return match($this->getPublicationStatus()) {
            'draft' => 'gray',
            'scheduled' => 'warning',
            'published' => 'success',
            default => 'gray'
        };
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

    /**
     * Calculate estimated reading time for the post
     *
     * @return string
     */
    public function getEstimatedReadingTime()
    {
        $readingSpeed = config('blogr.reading_speed.words_per_minute', 200);

        // Combine title and content for word count
        $text = $this->title . ' ' . $this->content;

        // Remove HTML tags and count words
        $plainText = strip_tags($text);
        $wordCount = str_word_count($plainText);

        // Calculate reading time in minutes (using floor instead of ceil for more precision)
        $minutes = floor($wordCount / $readingSpeed);

        // If less than 1 minute but has content, show as <1 minute
        if ($minutes < 1 && $wordCount > 0) {
            return '<1 minute';
        }

        // Return formatted time
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }

    /**
     * Get reading time with icon for display
     *
     * @return string
     */
    public function getReadingTimeWithIcon()
    {
        $time = $this->getEstimatedReadingTime();
        return $time;
    }

    /**
     * Get formatted reading time text using configuration
     *
     * @return string
     */
    public function getFormattedReadingTime()
    {
        if (!config('blogr.reading_time.enabled', true)) {
            return '';
        }

        $time = $this->getEstimatedReadingTime();
        $format = config('blogr.reading_time.text_format', 'Reading time: {time}');

        return str_replace('{time}', $time, $format);
    }
}
