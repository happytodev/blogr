<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category $category
 * @property int|null $author_bio_id
 */
class CategoryTranslation extends Model
{
    use HasFactory;

    protected $table = 'category_translations';

    protected $fillable = [
        'category_id',
        'locale',
        'name',
        'slug',
        'description',
        'author_bio_id',
    ];

    /**
     * The bio this translation prefers for the posts it groups (#376).
     *
     * @return BelongsTo<AuthorBio, $this>
     */
    public function authorBio(): BelongsTo
    {
        return $this->belongsTo(AuthorBio::class, 'author_bio_id');
    }

    /**
     * Get the category that owns this translation.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
