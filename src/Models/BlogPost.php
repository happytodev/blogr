<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            // Prevent writers from publishing posts
            if ($post->is_published && $post->user_id) {
                $user = User::find($post->user_id);
                if ($user && $user->hasRole('writer') && !$user->hasRole('admin')) {
                    throw new \Exception('Writers cannot publish posts. Only admins can publish.');
                }
            }

            // If post is published but no published_at date is set, set it to now
            if ($post->is_published && !$post->published_at) {
                $post->published_at = now();
            }
        });

        static::updating(function ($post) {
            // Prevent writers from publishing posts
            if ($post->is_published && $post->user_id) {
                $user = User::find($post->user_id);
                if ($user && $user->hasRole('writer') && !$user->hasRole('admin')) {
                    throw new \Exception('Writers cannot publish posts. Only admins can publish.');
                }
            }

            // If post is being published but no published_at date is set, set it to now
            if ($post->is_published && !$post->published_at) {
                $post->published_at = now();
            }
        });
    }

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
        // Use getOriginal to avoid triggering the content accessor
        $text = $this->title . ' ' . $this->getOriginal('content');

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

    /**
     * Get the frontmatter data for this post
     *
     * @return array
     */
    public function getFrontmatter()
    {
        $existingFrontmatter = $this->extractFrontmatter();

        $defaults = [
            'title' => $this->title,
            'slug' => $this->slug,
            'published' => $this->is_published,
            'published_at' => $this->published_at?->toISOString(),
            'category' => $this->category?->name,
            'tags' => $this->tags->pluck('name')->toArray(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'tldr' => $this->tldr,
        ];

        // Only set disable_toc default if it doesn't exist in existing frontmatter
        if (!isset($existingFrontmatter['disable_toc'])) {
            $defaults['disable_toc'] = false;
        }

        return array_merge($defaults, $existingFrontmatter);
    }

    /**
     * Extract frontmatter from content
     *
     * @return array
     */
    protected function extractFrontmatter()
    {
        if (!$this->content) {
            return [];
        }

        try {
            $document = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($this->content);
            return $document->matter();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get the content attribute - returns content without frontmatter for forms
     *
     * @param  string  $value
     * @return string
     */
    public function getContentAttribute($value)
    {
        // Only modify content for Filament admin forms to avoid recursion
        if (app()->runningInConsole() === false &&
            app()->bound('request') &&
            request()->is('admin/*') &&
            class_exists('\Filament\FilamentManager') &&
            !isset($this->attributes['__content_accessor_called'])) {

            // Prevent recursion by setting a flag
            $this->attributes['__content_accessor_called'] = true;

            try {
                $result = $this->getContentWithoutFrontmatter();
                unset($this->attributes['__content_accessor_called']);
                return $result;
            } catch (\Exception $e) {
                unset($this->attributes['__content_accessor_called']);
                return $value;
            }
        }

        return $value;
    }

    /**
     * Get the content without frontmatter
     *
     * @return string
     */
    public function getContentWithoutFrontmatter()
    {
        if (!$this->content) {
            return '';
        }

        try {
            $document = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($this->content);
            $body = $document->body();

            // Clean up leading whitespace that might be left after frontmatter extraction
            return ltrim($body, "\n\r");
        } catch (\Exception $e) {
            return $this->content;
        }
    }

    /**
     * Check if TOC is disabled for this post
     *
     * @return bool
     */
    public function isTocDisabled()
    {
        $frontmatter = $this->getFrontmatter();
        $value = $frontmatter['disable_toc'] ?? false;

        // Convert string values to boolean
        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) $value;
    }

    /**
     * Set TOC disabled status
     *
     * @param bool $disabled
     * @return void
     */
    public function setTocDisabled($disabled = true)
    {
        $frontmatter = $this->getFrontmatter();
        $frontmatter['disable_toc'] = (bool) $disabled;

        $this->updateContentWithFrontmatter($frontmatter);
    }

    /**
     * Update content with new frontmatter
     *
     * @param array $frontmatter
     * @return void
     */
    protected function updateContentWithFrontmatter(array $frontmatter)
    {
        $contentWithoutFrontmatter = $this->getContentWithoutFrontmatter();

        try {
            $yaml = \Symfony\Component\Yaml\Yaml::dump($frontmatter, 2, 2);
            $this->content = "---\n" . $yaml . "---\n\n" . $contentWithoutFrontmatter;
        } catch (\Exception $e) {
            // If YAML generation fails, keep original content
        }
    }

    /**
     * Get the content with frontmatter
     *
     * @return string
     */
    public function getContentWithFrontmatter()
    {
        $frontmatter = $this->getFrontmatter();
        $contentWithoutFrontmatter = $this->getContentWithoutFrontmatter();

        try {
            $yaml = \Symfony\Component\Yaml\Yaml::dump($frontmatter, 2, 2);
            return "---\n" . $yaml . "---\n\n" . $contentWithoutFrontmatter;
        } catch (\Exception $e) {
            return $this->getOriginal('content');
        }
    }

    /**
     * Check if TOC should be displayed for this post
     * Takes into account global settings and frontmatter override
     *
     * @return bool
     */
    public function shouldDisplayToc()
    {
        $globalEnabled = config('blogr.toc.enabled', true);
        $strictMode = config('blogr.toc.strict_mode', false);

        // If strict mode is enabled, always use global setting
        if ($strictMode) {
            return $globalEnabled;
        }

        // If not in strict mode, check frontmatter override
        $frontmatter = $this->extractFrontmatter();

        // If frontmatter explicitly sets disable_toc, use that value
        if (isset($frontmatter['disable_toc'])) {
            return !$frontmatter['disable_toc']; // disable_toc: true means TOC is disabled, so return false
        }

        // Otherwise, use global setting
        return $globalEnabled;
    }

    /**
     * Check if TOC toggle should be editable for this post
     * In strict mode, the toggle is not editable
     *
     * @return bool
     */
    public function isTocToggleEditable()
    {
        return !config('blogr.toc.strict_mode', false);
    }

    /**
     * Get the default TOC disabled state for new posts
     * Based on global settings
     *
     * @return bool
     */
    public static function getDefaultTocDisabled()
    {
        $globalEnabled = config('blogr.toc.enabled', true);
        return !$globalEnabled; // If global is enabled, TOC should be enabled (disabled = false)
    }

    /**
     * Check if TOC toggle should be editable for posts
     * Static version for use in forms
     *
     * @return bool
     */
    public static function isTocToggleEditableStatic()
    {
        return !config('blogr.toc.strict_mode', false);
    }
}
