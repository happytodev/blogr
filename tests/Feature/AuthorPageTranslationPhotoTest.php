<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create storage disk for testing
    Storage::fake('public');
    
    // Create a category
    $this->category = Category::factory()->create(['slug' => 'test-category']);
    
    // Create a user (author)
    $this->author = User::create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
        'password' => Hash::make('password'),
        'slug' => 'test-author',
    ]);
});

it('displays translation-specific photo on author page when available', function () {
    // Create a blog post WITHOUT pending translation data to avoid auto-creation
    $post = new BlogPost([
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => null, // No main photo
    ]);
    $post->save();
    
    // Create English translation with photo
    $translationPhoto = 'translations/en/test-photo.jpg';
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'English content',
        'photo' => $translationPhoto,
    ]);
    
    // Create French translation without photo
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'photo' => null,
    ]);
    
    // Visit author page in English
    $response = $this->get(route('blog.author', [
        'locale' => 'en',
        'userSlug' => $this->author->slug
    ]));
    
    $response->assertStatus(200);
    
    // Verify the translation photo is displayed
    $response->assertSee($translationPhoto);
    
    // Verify it's not showing the default image
    $response->assertDontSee(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg'));
});

it('displays translation photo even when main post has no photo on author page', function () {
    // Create a blog post WITHOUT a main photo
    $post = new BlogPost([
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => null, // Explicitly no photo
    ]);
    $post->save();
    
    // Create translation WITH a photo
    $translationPhoto = 'translations/special-article.jpg';
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Special Article',
        'slug' => 'special-article',
        'content' => 'Special content with image',
        'photo' => $translationPhoto,
    ]);
    
    // Visit author page
    $response = $this->get(route('blog.author', [
        'locale' => 'en',
        'userSlug' => $this->author->slug
    ]));
    
    $response->assertStatus(200);
    
    // The translation photo should be displayed
    $response->assertSee($translationPhoto);
    
    // Should NOT show the default image icon
    $response->assertDontSee('default-post.svg');
});

it('falls back to main post photo when translation has no photo on author page', function () {
    $mainPhoto = 'posts/main-photo.jpg';
    
    // Create a blog post WITH a main photo
    $post = new BlogPost([
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => $mainPhoto,
    ]);
    $post->save();
    
    // Create translation WITHOUT a photo
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Article Title',
        'slug' => 'article-title',
        'content' => 'Article content',
        'photo' => null, // No photo in translation
    ]);
    
    // Visit author page
    $response = $this->get(route('blog.author', [
        'locale' => 'en',
        'userSlug' => $this->author->slug
    ]));
    
    $response->assertStatus(200);
    
    // Should display the main post photo
    $response->assertSee('posts/main-photo.jpg');
});

it('uses same photo fallback logic as homepage on author page', function () {
    // Create post with translation-specific photo
    $post = new BlogPost([
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'photo' => 'posts/main.jpg',
    ]);
    $post->save();
    
    $translationPhoto = 'translations/specific.jpg';
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'photo' => $translationPhoto,
    ]);
    
    // Test homepage
    $homepageResponse = $this->get(route('blog.index', ['locale' => 'en']));
    $homepageResponse->assertStatus(200);
    $homepageResponse->assertSee($translationPhoto);
    
    // Test author page - should behave the same
    $authorResponse = $this->get(route('blog.author', [
        'locale' => 'en',
        'userSlug' => $this->author->slug
    ]));
    
    $authorResponse->assertStatus(200);
    $authorResponse->assertSee($translationPhoto);
    
    // Both should show translation photo, not main photo
    $homepageResponse->assertDontSee('posts/main.jpg', false);
    $authorResponse->assertDontSee('posts/main.jpg', false);
});
