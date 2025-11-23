<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;
use function Pest\Laravel\get;

uses(CmsWithLocalesTestCase::class);
uses()->group('cms', 'locales');

test('CMS homepage is accessible at locale root', function () {
    $page = CmsPage::factory()->homepage()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Welcome',
        'slug' => 'home',
    ]);
    
    $response = get('/en');
    
    $response->assertStatus(200);
    $response->assertSee('Welcome');
});

test('CMS page is accessible with locale prefix', function () {
    $page = CmsPage::factory()->published()->create();
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'About Us',
        'slug' => 'about',
    ]);
    
    $response = get('/en/about');
    
    $response->assertStatus(200);
    $response->assertSee('About Us');
});

test('CMS page falls back to default locale when translation missing', function () {
    $page = CmsPage::factory()->published()->create();
    
    // Only English translation
    $page->translations()->create([
        'locale' => 'en',
        'title' => 'About Us',
        'slug' => 'about',
    ]);
    
    // Request French (should fallback to English)
    $response = get('/fr/about');
    
    $response->assertStatus(200);
    $response->assertSee('About Us'); // English title
});
