<?php

use Illuminate\Support\Facades\Route;
use function Pest\Laravel\get;

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
    
    // Re-register service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // Test HTTP request to root - should redirect to locale
    $response = get('/');
    $response->assertRedirect('/en');
    
    // Test localized homepage works
    $response = get('/en');
    expect($response->status())->toBeIn([200, 404]);
});
