<?php

namespace Happytodev\Blogr\Observers;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;

class BlogPostObserver
{
    public function created(BlogPost $post): void
    {
        $this->syncDefaultTranslation($post);
    }

    public function updated(BlogPost $post): void
    {
        if ($this->hasRelevantChanges($post)) {
            $this->syncDefaultTranslation($post);
        }
    }

    protected function hasRelevantChanges(BlogPost $post): bool
    {
        $relevantFields = ['title', 'slug', 'content', 'tldr', 'meta_title', 'meta_description', 'meta_keywords'];
        
        foreach ($relevantFields as $field) {
            if ($post->isDirty($field)) {
                return true;
            }
        }
        
        return false;
    }

    protected function syncDefaultTranslation(BlogPost $post): void
    {
        $locale = $post->default_locale ?? config('blogr.locales.default', config('app.locale', 'en'));
        
        $translation = $post->translations()->where('locale', $locale)->first();
        
        if (!$translation) {
            $translation = new BlogPostTranslation();
            $translation->blog_post_id = $post->id;
            $translation->locale = $locale;
        }
        
        // Sync the translatable fields from the main post
        // Get raw attribute values without going through accessors
        $attributes = $post->getAttributes();
        
        $translation->title = $attributes['title'] ?? '';
        $translation->slug = $attributes['slug'] ?? '';
        $translation->content = $attributes['content'] ?? '';
        $translation->excerpt = $attributes['tldr'] ?? null;
        $translation->seo_title = $attributes['meta_title'] ?? null;
        $translation->seo_description = $attributes['meta_description'] ?? null;
        $translation->seo_keywords = $attributes['meta_keywords'] ?? null;
        
        // Calculate reading time using the translation model method
        if ($translation->content) {
            $translation->calculateReadingTime();
        }
        
        $translation->save();
    }

    public function deleting(BlogPost $post): void
    {
        $post->translations()->delete();
    }
}
