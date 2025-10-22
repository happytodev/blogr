<?php

namespace Happytodev\Blogr\Observers;

use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Illuminate\Support\Str;

class BlogSeriesTranslationObserver
{
    /**
     * Handle the BlogSeriesTranslation "created" event.
     * Update series slug from first translation title if slug is auto-generated.
     */
    public function created(BlogSeriesTranslation $translation): void
    {
        $series = $translation->series;
        
        if ($series) {
            // Check if slug looks auto-generated (starts with 'series-' and has timestamp)
            if (Str::startsWith($series->slug, 'series-') && Str::contains($series->slug, '-')) {
                // Check if this is the first translation
                $translationCount = $series->translations()->count();
                
                if ($translationCount === 1 && !empty($translation->title)) {
                    // Update slug from first translation title
                    $baseSlug = Str::slug($translation->title);
                    $slug = $baseSlug;
                    $counter = 1;
                    
                    // Ensure uniqueness
                    while (\Happytodev\Blogr\Models\BlogSeries::where('slug', $slug)->where('id', '!=', $series->id)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                    
                    $series->slug = $slug;
                    $series->save();
                }
            }
        }
    }
}
