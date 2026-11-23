<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A named bio an author can attach to a post (issue #373).
 *
 * `users.bio` remains the default bio per locale; these are the extra ones —
 * "artiste", "dev", "courte" — written in one language each.
 *
 * @property int $id
 * @property int $user_id
 * @property string $locale
 * @property string $label
 * @property string|null $body
 * @property bool|null $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 */
class AuthorBio extends Model
{
    protected $table = 'blogr_author_bios';

    protected $fillable = [
        'user_id',
        'locale',
        'label',
        'body',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Replace an author's bios with the rows a form holds, keeping one default
     * per language (the last one declared wins).
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function syncFor(int $userId, array $rows): void
    {
        static::query()->where('user_id', $userId)->delete();

        $defaults = [];

        foreach ($rows as $row) {
            if (empty($row['locale']) || empty($row['label'])) {
                continue;
            }

            $locale = is_string($row['locale']) ? $row['locale'] : '';
            $isDefault = (bool) ($row['is_default'] ?? false);

            if ($locale === '') {
                continue;
            }

            if ($isDefault && in_array($locale, $defaults, true)) {
                $isDefault = false;
            }

            static::query()->create([
                'user_id' => $userId,
                'locale' => $locale,
                'label' => is_string($row['label']) ? $row['label'] : '',
                'body' => isset($row['body']) && is_string($row['body']) ? $row['body'] : null,
                'is_default' => $isDefault,
            ]);

            if ($isDefault) {
                $defaults[] = $locale;
            }
        }
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }
}
