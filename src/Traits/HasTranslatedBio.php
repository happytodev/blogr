<?php

namespace Happytodev\Blogr\Traits;

use Happytodev\Blogr\Models\UserTranslation;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslatedBio
{
    /**
     * Get all bio translations for this user
     */
    public function bioTranslations(): HasMany
    {
        return $this->hasMany(UserTranslation::class, 'user_id');
    }

    /**
     * Get the bio for a specific locale
     */
    public function getBioForLocale(string $locale): ?string
    {
        $translation = $this->bioTranslations()->where('locale', $locale)->first();
        
        return $translation?->bio ?? $this->bio;
    }

    /**
     * Set the bio for a specific locale
     */
    public function setBioForLocale(string $locale, ?string $bio): void
    {
        $this->bioTranslations()->updateOrCreate(
            ['locale' => $locale],
            ['bio' => $bio]
        );
    }
}
