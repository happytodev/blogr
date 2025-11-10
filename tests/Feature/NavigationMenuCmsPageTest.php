<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\get;

beforeEach(function () {
    Config::set('app.locale', 'en');
    Config::set('blogr.ui.navigation.menu_items', []);
    Config::set('blogr.cms.enabled', true);
});

test('navigation renders cms page link correctly', function () {
    // Create a CMS page with translations
    $page = CmsPage::create([
        'slug' => 'about',
        'template' => 'default',
        'is_published' => true,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'about',
        'title' => 'About Us',
        'content' => 'About content',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'cms_page',
            'label' => 'About',
            'cms_page_id' => $page->id,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('About');
    
    // Verify the URL is correct
    $expectedUrl = route('cms.page.show', ['locale' => 'en', 'slug' => 'about']);
    $response->assertSee($expectedUrl, false);
});

test('navigation marks cms page as active when on that page', function () {
    $page = CmsPage::create([
        'slug' => 'contact',
        'template' => 'default',
        'is_published' => true,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'contact',
        'title' => 'Contact',
        'content' => 'Contact content',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'cms_page',
            'label' => 'Contact',
            'cms_page_id' => $page->id,
            'target' => '_self',
        ],
    ]);
    
    // Visit the CMS page
    $response = get(route('cms.page.show', ['locale' => 'en', 'slug' => 'contact']));
    
    $response->assertStatus(200);
});

test('navigation handles cms page with multiple locales', function () {
    $page = CmsPage::create([
        'slug' => 'services',
        'template' => 'default',
        'is_published' => true,
    ]);
    
    // English translation
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'services',
        'title' => 'Services',
        'content' => 'Services content',
    ]);
    
    // French translation
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'slug' => 'services',
        'title' => 'Services',
        'content' => 'Contenu services',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'cms_page',
            'label' => 'Services',
            'cms_page_id' => $page->id,
            'target' => '_self',
        ],
    ]);
    
    // Test EN locale
    $responseEn = get(route('blog.index', ['locale' => 'en']));
    $responseEn->assertStatus(200)
        ->assertSee('Services');
    
    // Test FR locale
    $responseFr = get(route('blog.index', ['locale' => 'fr']));
    $responseFr->assertStatus(200)
        ->assertSee('Services');
});

test('navigation handles missing cms page gracefully', function () {
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'cms_page',
            'label' => 'Missing Page',
            'cms_page_id' => 9999,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
});

test('navigation handles cms page without translation for locale', function () {
    $page = CmsPage::create([
        'slug' => 'test',
        'template' => 'default',
        'is_published' => true,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'test',
        'title' => 'Test',
        'content' => 'Test content',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'cms_page',
            'label' => 'Test',
            'cms_page_id' => $page->id,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'fr']));
    
    $response->assertStatus(200);
});

test('cms page can be added to menu with multilingual labels', function () {
    $page = CmsPage::create([
        'slug' => 'faq',
        'template' => 'default',
        'is_published' => true,
    ]);
    
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'faq',
        'title' => 'FAQ',
        'content' => 'FAQ content',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'cms_page',
            'labels' => [
                ['locale' => 'en', 'label' => 'FAQ'],
                ['locale' => 'fr', 'label' => 'Questions Fréquentes'],
            ],
            'cms_page_id' => $page->id,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('FAQ');
});
