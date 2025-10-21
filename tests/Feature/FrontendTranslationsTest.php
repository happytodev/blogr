<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
    config(['blogr.route.prefix' => 'blog']);
});

it('can display a blog post without locales enabled', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.route.homepage' => false]);
    
    // Re-register service provider with new config
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    $post = BlogPost::factory()->create([
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'content' => 'Original content here',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    $translation = $post->translations()->where('locale', 'en')->first();
    expect($translation)->not->toBeNull();
    
    $response = $this->get('/blog/original-slug');
    
    $response->assertStatus(200);
    $response->assertSee('Original Title');
    $response->assertSee('Original content');
});

it('can display a blog post with locales enabled', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.locales.available' => ['en', 'fr']]);
    
    // Re-register service provider to apply locale configuration
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content here',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    // Verify the Observer created 'en' translation
    $enTranslation = $post->translations()->where('locale', 'en')->first();
    expect($enTranslation)->not->toBeNull();
    expect($enTranslation->slug)->toBe('english-slug');
    
    $response = $this->get('/en/blog/english-slug');
    
    $response->assertStatus(200);
    $response->assertSee('English Title');
    $response->assertViewHas('currentLocale', 'en');
});

it('can display translated post in French', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.locales.available' => ['en', 'fr']]);
    
    // Re-register service provider to apply locale configuration
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu en français',
        'tldr' => 'Extrait français',
    ]);
    
    $response = $this->get('/fr/blog/titre-francais');
    
    $response->assertStatus(200);
    $response->assertSee('Titre Français');
    $response->assertViewHas('currentLocale', 'fr');
});

it('provides translations for language switcher', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.locales.available' => ['en', 'fr', 'es']]);
    
    // Re-register service provider to apply locale configuration
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'content' => 'English content',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    // Observer creates 'en' translation
    $enTranslation = $post->translations()->where('locale', 'en')->first();
    expect($enTranslation)->not->toBeNull();
    
    // Add French translation manually
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'titre-francais',
        'content' => 'Contenu français',
        'tldr' => 'Extrait français',
    ]);
    
    $response = $this->get('/en/blog/english-slug');
    
    $response->assertStatus(200);
    $response->assertViewHas('availableTranslations');
    
    $translations = $response->viewData('availableTranslations');
    expect($translations)->toHaveCount(2); // en + fr
});

it('returns 404 for missing translation', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    
    // Re-register service provider to apply locale configuration
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    $post = BlogPost::factory()->create([
        'title' => 'English Title',
        'slug' => 'english-slug',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    
    $response = $this->get('/fr/blog/titre-inexistant');
    
    $response->assertStatus(404);
});
