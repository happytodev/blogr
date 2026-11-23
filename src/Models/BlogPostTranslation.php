<?php

namespace Happytodev\Blogr\Models;

use Happytodev\Blogr\Tests\Database\Factories\BlogPostTranslationFactory;
use Happytodev\Blogr\Traits\ClearsLocaleCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $blog_post_id
 * @property string $locale
 * @property int|null $author_bio_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $tldr
 * @property string|null $photo
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property int|null $reading_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read BlogPost|null $post
 * @property-read AuthorBio|null $authorBio
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, Tag> $tags
 */
class BlogPostTranslation extends Model
{
    use HasFactory;

    protected $table = 'blog_post_translations';

    protected $fillable = [
        'blog_post_id',
        'locale',
        'author_bio_id',
        'title',
        'slug',
        'photo',
        'content',
        'excerpt',
        'tldr',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'reading_time',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return BlogPostTranslationFactory::new();
    }

    use ClearsLocaleCache;

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
    /**
     * The bio this translation shows, when the editor picked one (issue #373).
     *
     * @return BelongsTo<AuthorBio, $this>
     */
    public function authorBio(): BelongsTo
    {
        return $this->belongsTo(AuthorBio::class, 'author_bio_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
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
    /**
     * @return BelongsToMany<Tag, $this>
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
        $text = $this->title.' '.$this->content;

        // Remove HTML tags and count words
        $text = strip_tags($text);
        $wordCount = str_word_count($text);

        // Calculate reading time in minutes
        $readingTime = (int) ceil($wordCount / $readingSpeed);

        $this->reading_time = $readingTime;
        $this->save();
    }
}
