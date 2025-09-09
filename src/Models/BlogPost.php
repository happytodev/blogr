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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            // If post is published but no published_at date is set, set it to now
            if ($post->is_published && !$post->published_at) {
                $post->published_at = now();
            }
        });

        static::updating(function ($post) {
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

    /**
     * Get the frontmatter data for this post
     *
     * @return array
     */
    public function getFrontmatter()
    {
        $existingFrontmatter = $this->extractFrontmatter();

        return array_merge([
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
            'disable_toc' => false, // Default value
        ], $existingFrontmatter);
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

        // Check if content starts with frontmatter delimiter
        if (!str_starts_with(trim($this->content), '---')) {
            return [];
        }

        $lines = explode("\n", $this->content);
        $frontmatterLines = [];
        $inFrontmatter = false;
        $contentStartIndex = 0;

        foreach ($lines as $index => $line) {
            if ($line === '---') {
                if (!$inFrontmatter) {
                    $inFrontmatter = true;
                } else {
                    $contentStartIndex = $index + 1;
                    break;
                }
            } elseif ($inFrontmatter) {
                $frontmatterLines[] = $line;
            }
        }

        if (empty($frontmatterLines)) {
            return [];
        }

        $yaml = implode("\n", $frontmatterLines);

        try {
            return \Symfony\Component\Yaml\Yaml::parse($yaml) ?: [];
        } catch (\Exception $e) {
            return [];
        }
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

        // Check if content starts with frontmatter delimiter
        if (!str_starts_with(trim($this->content), '---')) {
            return $this->content;
        }

        $lines = explode("\n", $this->content);
        $inFrontmatter = false;
        $contentStartIndex = 0;

        foreach ($lines as $index => $line) {
            if ($line === '---') {
                if (!$inFrontmatter) {
                    $inFrontmatter = true;
                } else {
                    $contentStartIndex = $index + 1;
                    break;
                }
            }
        }

        return implode("\n", array_slice($lines, $contentStartIndex));
    }

    /**
     * Check if TOC is disabled for this post
     *
     * @return bool
     */
    public function isTocDisabled()
    {
        $frontmatter = $this->getFrontmatter();
        return $frontmatter['disable_toc'] ?? false;
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
            return $this->content;
        }
    }
}
