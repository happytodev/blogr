<?php

namespace Happytodev\Blogr\Http\Controllers;

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
        
        // Check if author profile feature is enabled
        if (!config('blogr.author_profile.enabled', true)) {
            abort(404, 'Author profiles are disabled');
        }
        
        // Get the User model class from config
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        
        // Find the author by slug
        $author = $userModel::where('slug', $actualSlug)->firstOrFail();
        
        // Get published posts by this author
        $posts = BlogPost::with(['category', 'tags', 'user'])
            ->where('user_id', $author->id)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(config('blogr.posts_per_page', 10));
        
        return view('blogr::author.show', [
            'author' => $author,
            'posts' => $posts,
            'currentLocale' => app()->getLocale(),
        ]);
    }
}
