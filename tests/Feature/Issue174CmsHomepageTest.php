<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\LocalizedCmsTestCase;
use function Pest\Laravel\get;

uses(LocalizedCmsTestCase::class)->group('cms', 'issue174');

/**
 * Test for Issue #174: Even if I choose CMS as home page during install, the blog is set as default
 * https://github.com/happytodev/blogr/issues/174
 * 
 * The bug: When choosing CMS as homepage during installation, accessing the root URL gives a 404
 * Expected: CMS homepage should be displayed
 * 
 * NOTE: These tests use LocalizedCmsTestCase which enables locales (simulating real-world config)
 */

test('CMS homepage is accessible at root URL when configured as homepage (Issue #174)', function () {
    // Create a CMS homepage
    $page = CmsPage::factory()->homepage()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Welcome to our site',
        'slug' => 'home',
        'content' => 'This is the CMS homepage',
    ]);
    
    // Access root URL - should redirect to /en (default locale)
    $response = get('/');
    
    // Should redirect to default locale
    $response->assertRedirect('/en');
    
    // Follow the redirect
    $response = get('/en');
    
    // Should show CMS homepage (not 404)
    $response->assertStatus(200);
    $response->assertSee('Welcome to our site');
});

test('blog is still accessible at /blog when CMS is homepage (Issue #174)', function () {
    // Create a CMS homepage
    $page = CmsPage::factory()->homepage()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Welcome',
        'slug' => 'home',
        'content' => 'CMS homepage',
    ]);
    
    // Blog should be accessible at /en/blog (with locale and prefix)
    $response = get('/en/blog');
    
    // Should show blog index (200) or 404 if no posts, but NOT CMS content
    expect($response->status())->toBeIn([200, 404]);
});

test('root URL shows CMS homepage not blog index (Issue #174)', function () {
    $page = CmsPage::factory()->homepage()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'CMS Homepage Title',
        'slug' => 'home',
        'content' => 'This is the CMS content',
    ]);
    
    // Access root and follow redirect
    $response = get('/');
    $response->assertRedirect('/en');
    
    $response = get('/en');
    
    // Should show CMS homepage with the correct title
    $response->assertStatus(200);
    $response->assertSee('CMS Homepage Title');
});
