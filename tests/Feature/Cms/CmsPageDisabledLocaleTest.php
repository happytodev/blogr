<?php

use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Tests\CmsWithLocalesTestCase;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\get;

uses(\Happytodev\Blogr\Tests\CmsWithLocalesTestCase::class);
uses()->group('cms');

beforeEach(function () {
    Config::set('blogr.locales.default', 'en');
    Config::set('blogr.locales.disabled', []);
    Config::set('blogr.ui.navigation.enabled', true);
    Config::set('blogr.ui.footer.enabled', true);
});

test('disabled locale returns 404 for CMS page', function () {
    Config::set('blogr.locales.disabled', ['fr']);

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

    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'title' => 'Page de Test',
        'slug' => 'page-test',
        'content' => 'Ceci est du contenu de test',
    ]);

    // English locale should work
    $response = get('/en/test-page');
    $response->assertStatus(200)
        ->assertSee('Test Page');

    // French locale should return 404
    $response = get('/fr/page-test');
    $response->assertStatus(404);
});

test('disabled locale returns 404 for CMS homepage', function () {
    Config::set('blogr.locales.disabled', ['fr']);

    $page = CmsPage::factory()->published()->create([
        'is_homepage' => true,
        'template' => CmsPageTemplate::DEFAULT,
    ]);

    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'title' => 'Home',
        'slug' => 'home',
        'content' => 'Welcome',
    ]);

    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'title' => 'Accueil',
        'slug' => 'accueil',
        'content' => 'Bienvenue',
    ]);

    // English homepage should work
    $response = get('/en');
    $response->assertStatus(200)
        ->assertSee('Home');

    // French homepage should return 404
    $response = get('/fr');
    $response->assertStatus(404);
});

test('all locales work when disabled list is empty', function () {
    Config::set('blogr.locales.disabled', []);

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

    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'title' => 'Page de Test',
        'slug' => 'page-test',
        'content' => 'Ceci est du contenu de test',
    ]);

    $response = get('/en/test-page');
    $response->assertStatus(200);

    $response = get('/fr/page-test');
    $response->assertStatus(200);
});
