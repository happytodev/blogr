<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// LocalizedTestCase is automatically used for tests in this folder (see tests/Pest.php)

it('uses translation-specific cover image when available', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'tech']);
    
    // Create main post with a main cover image
    $mainCover = UploadedFile::fake()->image('main-cover.jpg', 1200, 630);
    $mainCoverPath = $mainCover->store('post-covers', 'public');
    
    // Create translation-specific cover image
    $frCover = UploadedFile::fake()->image('fr-cover.jpg', 1200, 630);
    $frCoverPath = $frCover->store('post-covers', 'public');
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => $mainCoverPath,
        'default_locale' => 'en',
    ]);
    
    // Create French translation with its own cover image
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article en Français',
        'slug' => 'article-francais',
        'excerpt' => 'Résumé en français',
        'content' => 'Contenu en français',
        'photo' => $frCoverPath, // Translation-specific cover
    ]);
    
    // Create English translation without cover (should use main cover)
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Article in English',
        'slug' => 'article-english',
        'excerpt' => 'English excerpt',
        'content' => 'English content',
        'photo' => null, // No cover - should fallback to main
    ]);
    
    // Test French route - should use FR-specific cover
    $response = $this->get(route('blog.show', ['locale' => 'fr', 'slug' => 'article-francais']));
    $response->assertStatus(200);
    $response->assertSee(basename($frCoverPath), false);
    $response->assertDontSee(basename($mainCoverPath), false);
    
    // Test English route - should use main cover (fallback)
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'article-english']));
    $response->assertStatus(200);
    $response->assertSee(basename($mainCoverPath), false);
});

it('falls back to main post cover when translation has no cover image', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'tech']);
    
    $mainCover = UploadedFile::fake()->image('main-cover.jpg', 1200, 630);
    $mainCoverPath = $mainCover->store('post-covers', 'public');
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => $mainCoverPath, // Main cover image
        'default_locale' => 'en',
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article sans Cover',
        'slug' => 'article-sans-cover',
        'excerpt' => 'Résumé',
        'content' => 'Contenu',
        'photo' => null, // No translation cover
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'fr', 'slug' => 'article-sans-cover']));
    
    $response->assertStatus(200);
    // Should use main cover as fallback
    $response->assertSee('post-covers/', false);
    $response->assertSee(basename($mainCoverPath), false);
});

it('uses default cover when neither translation nor main post have cover', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'tech']);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => null, // No main cover
        'default_locale' => 'en',
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article sans Images',
        'slug' => 'article-sans-images',
        'excerpt' => 'Résumé',
        'content' => 'Contenu',
        'photo' => null, // No translation cover
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'fr', 'slug' => 'article-sans-images']));
    
    $response->assertStatus(200);
    // Should use default cover from config
    $defaultCover = config('blogr.posts.default_cover_image');
    if ($defaultCover) {
        $response->assertSee($defaultCover, false);
    }
});

it('displays correct cover image on post cards in index page', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'tech']);
    
    $mainCover = UploadedFile::fake()->image('main-cover.jpg', 1200, 630);
    $mainCoverPath = $mainCover->store('post-covers', 'public');
    
    $frCover = UploadedFile::fake()->image('fr-cover.jpg', 1200, 630);
    $frCoverPath = $frCover->store('post-covers', 'public');
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => $mainCoverPath,
        'default_locale' => 'en',
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Article Français',
        'slug' => 'article-francais',
        'excerpt' => 'Résumé français',
        'content' => 'Contenu français',
        'photo' => $frCoverPath,
    ]);
    
    // Visit French index page
    $response = $this->get(route('blog.index', ['locale' => 'fr']));
    $response->assertStatus(200);
    // Should show FR cover in post card
    $response->assertSee(basename($frCoverPath), false);
});
