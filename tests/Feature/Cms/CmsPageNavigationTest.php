<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\get;

uses(CmsTestCase::class);
uses()->group('cms');

beforeEach(function () {
    Config::set('blogr.locales.enabled', false);
    Config::set('blogr.locales.default', 'en');
    Config::set('blogr.ui.navigation.enabled', true);
    Config::set('blogr.ui.footer.enabled', true);
});

test('CMS page includes navigation component', function () {
    $page = CmsPage::factory()->create([
        'is_published' => true,
        'template' => CmsPageTemplate::DEFAULT,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'This is test content',
    ]);
    
    $response = get('/test-page');
    
    $response->assertStatus(200)
        ->assertSee('Test Page')
        // Check for navigation element
        ->assertSee('<nav', false);
});

test('CMS page includes footer component', function () {
    $page = CmsPage::factory()->create([
        'is_published' => true,
        'template' => CmsPageTemplate::DEFAULT,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'This is test content',
    ]);
    
    $response = get('/test-page');
    
    $response->assertStatus(200)
        // Check for footer element (assuming it has a footer tag or specific class)
        ->assertSee('<footer', false);
});

test('CMS page respects navigation disabled setting', function () {
    Config::set('blogr.ui.navigation.enabled', false);
    
    $page = CmsPage::factory()->create([
        'is_published' => true,
        'template' => CmsPageTemplate::DEFAULT,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'This is test content',
    ]);
    
    $response = get('/test-page');
    
    $response->assertStatus(200)
        ->assertSee('Test Page');
    
    // Navigation should not be present
    $content = $response->getContent();
    // The nav element might still exist but should not have menu items
    expect($content)->not->toContain('class="container mx-auto px-4"');
});
