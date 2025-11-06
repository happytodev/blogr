<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithPrefixTestCase;
use function Pest\Laravel\get;

uses(CmsWithPrefixTestCase::class);
uses()->group('cms', 'prefix');

test('CMS page is accessible with prefix', function () {
    $page = CmsPage::factory()->published()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'About Us',
        'slug' => 'about',
        'content' => 'About content',
    ]);
    
    $response = get('/page/about');
    
    $response->assertStatus(200);
    $response->assertSee('About Us');
});

test('CMS page is not accessible without prefix', function () {
    $page = CmsPage::factory()->published()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Contact',
        'slug' => 'contact',
        'content' => 'Contact content',
    ]);
    
    // Should not work without prefix
    $response = get('/contact');
    
    $response->assertStatus(404);
});

test('reserved slugs can be used with prefix', function () {
    // With prefix, 'blog' slug should still be blocked at model level
    expect(function () {
        CmsPage::factory()->create(['slug' => 'blog']);
    })->toThrow(\InvalidArgumentException::class);
    
    // Regular slug works fine
    $page = CmsPage::factory()->published()->create(['slug' => 'my-blog-page']);
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Blog Page',
        'slug' => 'my-blog-page',
        'content' => 'Custom blog page',
    ]);
    
    $response = get('/page/my-blog-page');
    $response->assertStatus(200);
});
