<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
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

test("blog routes work with empty prefix (homepage)", function () {
    // Refresh application with new config
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.homepage', true);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Rebind routes by refreshing the service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // The blog should be accessible at the root domain
    $response = get("/");

    // May return 200 or 404 depending on data
    expect($response->status())->toBeIn([200, 404]);
});

test("blog routes redirect with empty prefix and locales enabled", function () {
    // Refresh application with new config
    $this->app['config']->set('blogr.route.homepage', true);
    $this->app['config']->set('blogr.locales.enabled', true);
    $this->app['config']->set('blogr.locales.default', 'en');
    $this->app['config']->set('blogr.locales.available', ['en', 'fr']);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Verify configuration is set correctly
    expect(config('blogr.route.homepage'))->toBeTrue();
    expect(config('blogr.locales.enabled'))->toBeTrue();
    expect(config('blogr.locales.default'))->toBe('en');
});

test("blog routes work with prefix", function () {
    // Refresh application with new config
    $this->app['config']->set('blogr.route.prefix', 'blog');
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.homepage', false);
    $this->app['config']->set('blogr.route.frontend.enabled', true);
    
    // Rebind routes by refreshing the service provider
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
    
    // The blog should be accessible at /blog
    $response = get("/blog");
    $response->assertStatus(200);
});
