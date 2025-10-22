<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Helpers\ConfigHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogPost extends Model
{
    use HasFactory;
    
    /**
     * Temporary storage for translatable fields during creation
     */
    protected $pendingTranslationData = [];
    
    protected $fillable = [
        'photo',
        'user_id',
        'is_published',
        'published_at',
        'category_id',
        'blog_series_id',
        'series_position',
        'default_locale',
        'display_toc',
        // Translatable fields (for backward compatibility - will be moved to translations)
        'title',
        'slug',
        'content',
        'tldr',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'display_toc' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Happytodev\Blogr\Tests\Database\Factories\BlogPostFactory::new();
    }

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
            
            // Store translatable fields BEFORE they're removed
            $translatableFields = ['title', 'slug', 'content', 'tldr', 'meta_title', 'meta_description', 'meta_keywords'];
            foreach ($translatableFields as $field) {
                if (isset($post->attributes[$field])) {
                    $post->pendingTranslationData[$field] = $post->attributes[$field];
                    unset($post->attributes[$field]);
                }
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
        
        // After creating a post, create translation from pending data
        static::created(function ($post) {
            // If we have pending translatable data, create a translation
            if (!empty($post->pendingTranslationData)) {
                $locale = $post->default_locale ?? config('app.locale', 'en');
                
                // Map old field names to new ones
                $fieldMapping = [
                    'meta_title' => 'seo_title',
                    'meta_description' => 'seo_description',
                    'meta_keywords' => 'seo_keywords',
                ];
                
                $translationData = [];
                foreach ($post->pendingTranslationData as $key => $value) {
                    // Map old field name to new one if needed
                    $newKey = $fieldMapping[$key] ?? $key;
                    $translationData[$newKey] = $value;
                }
                
                $post->translations()->create(array_merge([
                    'locale' => $locale,
                ], $translationData));
                
                // Clear pending data
                $post->pendingTranslationData = [];
                
                // Reload translations to make them available in accessors
                $post->load('translations');
            }
        });
        
        // Before deleting a post, ensure translations are deleted (for SQLite compatibility)
        static::deleting(function ($post) {
            $post->translations()->delete();
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

    // Alias for user() - more semantic for blog context
    public function author()
    {
        return $this->user();
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
     * A blog post belongs to a series
     */
    public function series()
    {
        return $this->belongsTo(BlogSeries::class, 'blog_series_id');
    }

    /**
     * A blog post has many translations
     */
    public function translations()
    {
        return $this->hasMany(BlogPostTranslation::class, 'blog_post_id');
    }

    /**
     * Get the translation for a specific locale
     */
    public function translate(string $locale): ?BlogPostTranslation
    {
        // If translations are already loaded, use the collection
        if ($this->relationLoaded('translations')) {
            return $this->translations->where('locale', $locale)->first();
        }
        
        // Otherwise, query the database
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Alias for translate() method
     */
    public function getTranslation(string $locale): ?BlogPostTranslation
    {
        return $this->translate($locale);
    }

    /**
     * Get the default translation
     */
    public function getDefaultTranslation(): ?BlogPostTranslation
    {
        $locale = $this->default_locale ?? config('app.locale', 'en');
        return $this->translate($locale) ?? $this->translations()->first();
    }

    /**
     * Accesseur pour obtenir le titre depuis la traduction par défaut
     */
    public function getTitleAttribute($value): ?string
    {
        // Si on a déjà la valeur en DB (ancien système), la retourner
        if ($value) {
            return $value;
        }
        
        // Sinon, récupérer depuis la traduction
        $translation = $this->getDefaultTranslation();
        return $translation?->title;
    }

    /**
     * Get the slug for the default locale
     */
    public function getSlugAttribute($value): ?string
{
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->slug;
    }

    /**
     * Get the TLDR attribute (with backward compatibility)
     */
    public function getTldrAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->tldr;
    }

    /**
     * Get the reading time attribute (with backward compatibility)
     */
    public function getReadingTimeAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->reading_time;
    }

    /**
     * Get the meta_title attribute from translation (maps to seo_title)
     */
    public function getMetaTitleAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->seo_title;
    }

    /**
     * Get the meta_description attribute from translation (maps to seo_description)
     */
    public function getMetaDescriptionAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->seo_description;
    }

    /**
     * Get the meta_keywords attribute from translation (maps to seo_keywords)
     */
    public function getMetaKeywordsAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        $translation = $this->getDefaultTranslation();
        return $translation?->seo_keywords;
    }

    /**
     * Get the photo URL attribute.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            // Use the 'public' disk for post images
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            
            try {
                // Try to generate temporary URL (works for S3, etc.)
                return $disk->temporaryUrl(
                    $this->photo,
                    now()->addHours(1)
                );
            } catch (\RuntimeException $e) {
                // Fallback to regular URL for local driver
                return $disk->url($this->photo);
            }
        }
        
        // Return default post image from config. Prefer the new posts.default_image
        // but keep backward compatibility with legacy 'blogr.default_cover_image'.
        $defaultImage = config('blogr.posts.default_image')
            ?? config('blogr.default_cover_image')
            ?? '/vendor/blogr/images/default-post.svg';

        return asset($defaultImage);
    }

    /**
     * Get the next post in the series
     */
    public function nextInSeries(): ?BlogPost
    {
        if (!$this->blog_series_id || !$this->series_position) {
            return null;
        }

        return static::where('blog_series_id', $this->blog_series_id)
            ->where('series_position', '>', $this->series_position)
            ->orderBy('series_position')
            ->first();
    }

    /**
     * Get the previous post in the series
     */
    public function previousInSeries(): ?BlogPost
    {
        if (!$this->blog_series_id || !$this->series_position) {
            return null;
        }

        return static::where('blog_series_id', $this->blog_series_id)
            ->where('series_position', '<', $this->series_position)
            ->orderBy('series_position', 'desc')
            ->first();
    }

    /**
     * Get the complete series navigation
     */
    public function getSeriesNavigation(): ?array
    {
        if (!$this->blog_series_id) {
            return null;
        }

        return [
            'previous' => $this->previousInSeries(),
            'current' => $this,
            'next' => $this->nextInSeries(),
            'all' => static::where('blog_series_id', $this->blog_series_id)
                ->orderBy('series_position')
                ->get(),
        ];
    }

    /**
     * Get estimated reading time in minutes (raw number)
     *
     * @return int
     */
    public function getEstimatedReadingTimeMinutes(): int
    {
        $readingSpeed = config('blogr.reading_speed.words_per_minute', 200);

        // Try to use current translation's content if available
        $text = '';
        
        // Check if we have a loaded translation with content
        if ($this->relationLoaded('translations')) {
            $currentLocale = app()->getLocale();
            $translation = $this->translations->firstWhere('locale', $currentLocale);
            
            if ($translation && $translation->content) {
                $text = ($translation->title ?? '') . ' ' . $translation->content;
            }
        }
        
        // Fallback to main table content if no translation content found
        if (empty($text)) {
            $text = $this->title . ' ' . $this->getOriginal('content');
        }

        // Remove HTML tags and count words
        $plainText = strip_tags($text);
        $wordCount = str_word_count($plainText);

        // Calculate reading time in minutes
        $minutes = floor($wordCount / $readingSpeed);

        // Minimum 1 minute if has content
        return $wordCount > 0 ? max(1, (int)$minutes) : 0;
    }

    /**
     * Calculate estimated reading time for the post (legacy string format)
     *
     * @return string
     */
    public function getEstimatedReadingTime()
    {
        $minutes = $this->getEstimatedReadingTimeMinutes();

        if ($minutes === 0) {
            return '0 minutes';
        }

        if ($minutes < 1) {
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
     * Uses the translation's stored reading_time value if available,
     * otherwise falls back to calculation from main content
     *
     * @return string
     */
    public function getFormattedReadingTime()
    {
        if (!config('blogr.reading_time.enabled', true)) {
            return '';
        }

        // Use the reading_time attribute (set by accessors or controllers)
        // This allows translations to provide their own reading time
        $minutes = $this->reading_time ?? $this->getEstimatedReadingTimeMinutes();
        
        return ConfigHelper::getReadingTimeText($minutes);
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

        // For frontend: if no value in DB, check translations
        if (!$value) {
            $translation = $this->getDefaultTranslation();
            return $translation?->content;
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
        // Get content from DB or translations
        $content = $this->attributes['content'] ?? null;
        
        if (!$content) {
            $translation = $this->getDefaultTranslation();
            $content = $translation?->content;
        }
        
        if (!$content) {
            return '';
        }

        try {
            $document = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($content);
            $body = $document->body();

            // Clean up leading whitespace that might be left after frontmatter extraction
            return ltrim($body, "\n\r");
        } catch (\Exception $e) {
            return $content;
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
     * Priority: strict_mode global > display_toc field > frontmatter disable_toc > global setting
     *
     * @return bool
     */
    public function shouldDisplayToc()
    {
        $globalEnabled = config('blogr.toc.enabled', true);
        $strictMode = config('blogr.toc.strict_mode', false);

        // If strict mode is enabled, always use global setting (highest priority)
        if ($strictMode) {
            return $globalEnabled;
        }

        // Second check: use display_toc field from database if explicitly set
        if ($this->display_toc !== null) {
            return (bool) $this->display_toc;
        }

        // Third check: legacy frontmatter support
        $frontmatter = $this->extractFrontmatter();
        if (isset($frontmatter['disable_toc'])) {
            return !$frontmatter['disable_toc'];
        }

        // Fourth check: global setting
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
