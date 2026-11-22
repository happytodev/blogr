<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tag_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tag $tag
 */
class TagTranslation extends Model
{
    use HasFactory;

    protected $table = 'tag_translations';

    protected $fillable = [
        'tag_id',
        'locale',
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the tag that owns this translation.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
