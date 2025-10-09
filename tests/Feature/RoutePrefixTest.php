<?php

use function Pest\Laravel\get;

test("blog routes work with empty prefix (homepage)", function () {
    // Refresh application with new config
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.homepage', true);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Rebind routes by refreshing the service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // The blog should be accessible at the root domain
    $response = get("/");

    // Should not redirect (since locales are disabled)
    $response->assertStatus(200);
});

test("blog routes redirect with empty prefix and locales enabled", function () {
    // Refresh application with new config
    $this->app['config']->set('blogr.route.homepage', true);
    $this->app['config']->set('blogr.locales.enabled', true);
    $this->app['config']->set('blogr.locales.default', 'en');
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Rebind routes by refreshing the service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);

    // Should redirect root to /en
    $response = get("/");
    $response->assertRedirect("/en");
});

test("blog routes work with prefix", function () {
    // Refresh application with new config
    $this->app['config']->set('blogr.route.prefix', 'blog');
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.homepage', false);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Rebind routes by refreshing the service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);

    // Should work with /blog prefix
    $response = get("/blog");
    $response->assertStatus(200);
});

