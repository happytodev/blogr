<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

test('translation uses its own photo when available', function () {
    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'photo' => 'blog-photos/main-image.jpg',
    ]);
    
    $enTranslation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'English content',
        'photo' => 'blog-photos/en-specific-image.jpg',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'english-title']));
    
    $response->assertOk();
    expect($response->viewData('post')->photo_url)->toContain('en-specific-image.jpg');
});

test('translation falls back to main post photo when translation has no photo', function () {
    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'photo' => 'blog-photos/main-image.jpg',
    ]);
    
    $frTranslation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'photo' => null,
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'fr', 'slug' => 'titre-francais']));
    
    $response->assertOk();
    expect($response->viewData('post')->photo_url)->toContain('main-image.jpg');
});

test('translation falls back to another translation photo when main post has no photo', function () {
    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'photo' => null,
    ]);
    
    $enTranslation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'English content',
        'photo' => 'blog-photos/en-image.jpg',
    ]);
    
    $frTranslation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'photo' => null,
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'fr', 'slug' => 'titre-francais']));
    
    $response->assertOk();
    expect($response->viewData('post')->photo_url)->toContain('en-image.jpg');
});

test('post uses default cover image when no photos are available anywhere', function () {
    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'photo' => null,
    ]);
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Title Without Photo',
        'slug' => 'title-without-photo',
        'content' => 'Content',
        'photo' => null,
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'title-without-photo']));
    
    $response->assertOk();
    // Should return default cover image from config
    $defaultCover = config('blogr.default_cover_image', '/images/default-cover.svg');
    expect($response->viewData('post')->photo_url)->toContain($defaultCover);
});

test('photo field is fillable in BlogPostTranslation model', function () {
    $fillable = (new BlogPostTranslation())->getFillable();
    
    expect($fillable)->toContain('photo');
});

test('translation photo can be saved and retrieved', function () {
    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);
    
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Test Title',
        'slug' => 'test-title',
        'content' => 'Test content',
        'photo' => 'blog-photos/translation-photo.jpg',
    ]);
    
    $translation->refresh();
    
    expect($translation->photo)->toBe('blog-photos/translation-photo.jpg');
});

test('homepage index shows translation specific photo in EN cards', function () {
    $post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'photo' => 'photos/main-photo.jpg',
    ]);

    // Create EN translation with its own photo
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'English Title',
        'slug' => 'english-title',
        'content' => 'Content',
        'excerpt' => 'Excerpt',
        'photo' => 'photos/en-photo.jpg', // Translation-specific photo
    ]);

    // Create FR translation without photo
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu',
        'excerpt' => 'Extrait',
    ]);

    // Test EN homepage - should show EN translation photo
    $response = $this->get(route('blog.index'));
    $posts = $response->viewData('posts');
    $firstPost = $posts->first();
    
    expect($firstPost->photo_url)->toContain('en-photo.jpg');
});
