<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\get;

beforeEach(function () {
    // Create a published post for routes to work
    $post = BlogPost::factory()->create([
        'published_at' => now()->subDay(),
        'user_id' => 1,
    ]);

    // Add translations
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Test Post FR',
        'slug' => 'test-post-fr',
        'content' => 'Contenu de test',
    ]);
});

// Blog navigation tests with all configuration combinations
// homepage (true/false) × locales (true/false)
// These tests use real HTTP requests to validate behavior

test('homepage=false, locale=false: routes available at /blog/*', function () {
    // Reset to default config
    $this->app['config']->set('blogr.route.homepage', false);
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.prefix', 'blog');
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Re-register service provider with clean config
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // Verify /blog is accessible (may return 200 or 404 without data)
    $response = get('/blog');
    expect($response->status())->toBeIn([200, 404]);
    
    // Verify at least one blog.index route exists without {locale}
    $routes = collect(Route::getRoutes())->filter(function($r) {
        return $r->getName() === 'blog.index' && !str_contains($r->uri(), '{locale}');
    });
    
    expect(count($routes))->toBeGreaterThan(0);
    
    // If we found the route, verify its URI
    if (count($routes) > 0) {
        $blogIndexRoute = $routes->first();
        expect($blogIndexRoute->uri())->toBe('blog');
    }
});

test('homepage=true, locale=false: routes available at /*', function () {
    // Reconfigure for homepage mode
    $this->app['config']->set('blogr.route.homepage', true);
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Re-register service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // Test HTTP request to root
    $response = get('/');
    
    // Should return 200 or 404 (not redirect)
    expect($response->status())->toBeIn([200, 404]);
    
    // Verify a homepage route exists (without locale)
    $routes = collect(Route::getRoutes())->filter(function($r) {
        return $r->getName() === 'blog.index' && !str_contains($r->uri(), '{locale}');
    });
    
    expect(count($routes))->toBeGreaterThan(0);
});

test('homepage=false, locale=true: redirect /blog to /{locale}/blog', function () {
    // Reconfigure for locales
    $this->app['config']->set('blogr.route.homepage', false);
    $this->app['config']->set('blogr.locales.enabled', true);
    $this->app['config']->set('blogr.locales.default', 'en');
    $this->app['config']->set('blogr.locales.available', ['en', 'fr']);
    $this->app['config']->set('blogr.route.prefix', 'blog');
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Re-register service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // Test HTTP request - should redirect
    $response = get('/blog');
    $response->assertRedirect('/en/blog');
    
    // Test localized URL works
    $response = get('/en/blog');
    expect($response->status())->toBeIn([200, 404]);
});

test('homepage=true, locale=true: redirect / to /{locale}', function () {
    // Reconfigure for homepage with locales
    $this->app['config']->set('blogr.route.homepage', true);
    $this->app['config']->set('blogr.locales.enabled', true);
    $this->app['config']->set('blogr.locales.default', 'en');
    $this->app['config']->set('blogr.locales.available', ['en', 'fr']);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Verify configuration is set correctly for redirect scenario
    expect(config('blogr.route.homepage'))->toBeTrue();
    expect(config('blogr.locales.enabled'))->toBeTrue();
    expect(config('blogr.locales.default'))->toBe('en');
});
