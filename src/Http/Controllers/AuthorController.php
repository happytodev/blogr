<?php

namespace Happytodev\Blogr\Http\Controllers;

use Happytodev\Blogr\Helpers\MarkdownHelper;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AuthorController extends Controller
{
    /**
     * Display the author's profile page with their published posts
     */
    public function show(string $localeOrSlug, ?string $userSlug = null): View
    {
        // Determine if locales are enabled and adjust parameters accordingly
        // When locales are enabled: $localeOrSlug = locale, $userSlug = slug
        // When locales are disabled: $localeOrSlug = slug, $userSlug = null
        $actualSlug = $userSlug ?? $localeOrSlug;
        $locale = $userSlug ? $localeOrSlug : app()->getLocale();
        
        // Check if author profile feature is enabled
        if (!config('blogr.author_profile.enabled', true)) {
            abort(404, 'Author profiles are disabled');
        }
        
        // Get the User model class from config
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        
        // Find the author by slug
        $author = $userModel::where('slug', $actualSlug)->firstOrFail();
        
        // Ensure bio is cast as array if it's JSON
        if (is_string($author->bio) && str_starts_with($author->bio, '{')) {
            $author->bio = json_decode($author->bio, true);
        }
        
        // Get published posts by this author with translations
        $posts = BlogPost::with([
                'category.translations', 
                'tags.translations', 
                'translations',
                'user'
            ])
            ->where('user_id', $author->id)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(config('blogr.posts_per_page', 10))
            ->through(function ($post) use ($locale) {
                // Get the translation for this locale
                $translation = $post->translations->firstWhere('locale', $locale);
                
                // If no translation in requested locale, try default translation
                if (!$translation) {
                    $translation = $post->getDefaultTranslation();
                }
                
                // Override post attributes with translation, with fallback to model accessors
                $post->translated_title = $translation?->title ?? $post->title;
                $post->translated_slug = $translation?->slug ?? $post->slug;
                $post->translated_tldr = $translation?->tldr ?? $post->tldr;
                
                // Calculate reading time from translation content
                if ($translation && $translation->content) {
                    $readingSpeed = config('blogr.reading_speed.words_per_minute', 200);
                    $text = ($translation->title ?? '') . ' ' . $translation->content;
                    $plainText = strip_tags($text);
                    $wordCount = str_word_count($plainText);
                    $minutes = floor($wordCount / $readingSpeed);
                    $post->reading_time = $wordCount > 0 ? max(1, (int)$minutes) : 0;
                } else {
                    $post->reading_time = 0;
                }
                
                // Photo fallback logic: translation photo > post photo > any other translation photo
                $photoToUse = null;
                
                if ($translation?->photo) {
                    $photoToUse = $translation->photo;
                } elseif ($post->photo) {
                    $photoToUse = $post->photo;
                } else {
                    $anyTranslationWithPhoto = $post->translations->first(fn($t) => !empty($t->photo));
                    if ($anyTranslationWithPhoto) {
                        $photoToUse = $anyTranslationWithPhoto->photo;
                    }
                }
                
                // Set the photo to use (this will override the accessor's default behavior)
                // This is the same approach as in BlogController::index()
                if ($photoToUse) {
                    $post->setAttribute('photo', $photoToUse);
                }
                
                return $post;
            });
        
        // Extract and render bio markdown
        $bioHtml = '';
        if (!empty($author->bio)) {
            if (is_array($author->bio)) {
                $bioText = $author->bio[$locale] ?? $author->bio[config('blogr.locales.default', 'en')] ?? '';
            } else {
                $bioText = $author->bio;
            }
            
            if (!empty($bioText)) {
                $bioHtml = MarkdownHelper::toHtml($bioText);
            }
        }
        
        return view('blogr::author.show', [
            'author' => $author,
            'posts' => $posts,
            'currentLocale' => $locale,
            'bioHtml' => $bioHtml,
        ]);
    }
}
