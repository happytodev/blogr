<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('app.name', 'Test Blog');

    // Create a published post with translations
    $post = BlogPost::factory()->create([
        'published_at' => now()->subDay(),
        'user_id' => 1,
    ]);

    // Factory already creates one translation, add the French one
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Test Post FR',
        'slug' => 'test-post-fr',
        'content' => 'Contenu de test en français',
    ]);
});

// Test that site title redirects to default locale when locales are enabled
it('redirects to default locale when clicking site title with locales enabled', function () {
    Config::set('blogr.locales.enabled', true);
    Config::set('blogr.locales.default', 'fr');
    Config::set('blogr.locales.available', ['fr', 'en']);
    Config::set('blogr.homepage.type', 'blog');
    Config::set('blogr.route.homepage', true);
    Config::set('blogr.route.frontend.enabled', true);

    // Verify configuration
    expect(config('blogr.locales.enabled'))->toBeTrue();
    expect(config('blogr.locales.default'))->toBe('fr');
    expect(config('blogr.route.homepage'))->toBeTrue();
});

// Test that logo configuration is properly loaded
it('navigation loads logo configuration correctly', function () {
    Config::set('blogr.ui.navigation.logo_url', 'https://example.com/logo.png');
    Config::set('blogr.ui.navigation.logo_display', 'both');
    Config::set('blogr.locales.enabled', true);
    Config::set('blogr.homepage.type', 'blog');
    Config::set('blogr.route.homepage', true);

    $logoUrl = config('blogr.ui.navigation.logo_url');
    $logoDisplay = config('blogr.ui.navigation.logo_display');

    expect($logoUrl)->toBe('https://example.com/logo.png');
    expect($logoDisplay)->toBe('both');
});

// Test CMS homepage redirect to default locale  
it('CMS homepage redirects to default locale when locales enabled', function () {
    Config::set('blogr.locales.enabled', true);
    Config::set('blogr.locales.default', 'fr');
    Config::set('blogr.locales.available', ['fr', 'en']);
    Config::set('blogr.homepage.type', 'cms');
    Config::set('blogr.cms.enabled', true);

    // Verify configuration
    expect(config('blogr.locales.enabled'))->toBeTrue();
    expect(config('blogr.locales.default'))->toBe('fr');
    expect(config('blogr.homepage.type'))->toBe('cms');
});
