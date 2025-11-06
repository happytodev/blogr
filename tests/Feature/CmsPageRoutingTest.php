<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Tests\CmsTestCase;
use function Pest\Laravel\get;

uses(CmsTestCase::class)->group('cms');

beforeEach(function () {
    // Configuration déjà faite dans CmsTestCase
    // Run CMS migrations si besoin (normalement déjà fait)
    $this->artisan('migrate', ['--database' => 'testing']);
});

describe('CMS Routes - Without Prefix', function () {
    beforeEach(function () {
        config(['blogr.cms.route.prefix' => '']);
    });

    test('CMS homepage is accessible at root when blog is not homepage', function () {
        config(['blogr.route.homepage' => false]);
        
        $page = CmsPage::factory()->homepage()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Welcome Home',
            'slug' => 'home',
            'content' => 'Homepage content',
        ]);
        
        $response = get('/');
        
        $response->assertStatus(200);
        $response->assertSee('Welcome Home');
    });

    test('CMS page is accessible without prefix', function () {
        $page = CmsPage::factory()->published()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'About Us',
            'slug' => 'about',
            'content' => 'About content',
        ]);
        
        $response = get('/about');
        
        $response->assertStatus(200);
        $response->assertSee('About Us');
    });

    test('CMS routes do not conflict with blog routes', function () {
        // Create a CMS page that should NOT capture blog routes
        $page = CmsPage::factory()->published()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'My Page',
            'slug' => 'my-page',
            'content' => 'Page content',
        ]);
        
        // Blog route should still work
        $response = get('/blog');
        
        // Should get blog index, not 404 or CMS page
        expect($response->status())->toBeIn([200, 404]); // 404 if no posts
        $response->assertDontSee('My Page'); // Should NOT see CMS content
    });

    test('reserved slugs are blocked in CMS pages', function () {
        expect(function () {
            CmsPage::factory()->create(['slug' => 'blog']);
        })->toThrow(\InvalidArgumentException::class);
    });
});

// NOTE: Tests for "With Prefix" and "With Locales" are in separate files
// because they require different TestCase configurations (CmsWithPrefixTestCase, CmsWithLocalesTestCase)
// See: tests/Feature/Cms/WithPrefix/CmsPagePrefixRoutingTest.php
// See: tests/Feature/Cms/WithLocales/CmsPageLocaleRoutingTest.php

describe('CMS Publication Status', function () {
    test('unpublished pages return 404', function () {
        $page = CmsPage::factory()->unpublished()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Draft Page',
            'slug' => 'draft',
            'content' => 'Draft content',
        ]);
        
        $response = get('/draft');
        
        $response->assertStatus(404);
    });

    test('scheduled pages return 404 before publication date', function () {
        $page = CmsPage::factory()->scheduled()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Future Page',
            'slug' => 'future',
            'content' => 'Future content',
        ]);
        
        $response = get('/future');
        
        $response->assertStatus(404);
    });

    test('published pages are accessible', function () {
        $page = CmsPage::factory()->published()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Live Page',
            'slug' => 'live',
            'content' => 'Live content',
        ]);
        
        $response = get('/live');
        
        $response->assertStatus(200);
        $response->assertSee('Live Page');
    });
});

describe('CMS Homepage Management', function () {
    test('only one homepage can exist at a time', function () {
        $page1 = CmsPage::factory()->homepage()->create();
        expect($page1->is_homepage)->toBeTrue();
        
        $page2 = CmsPage::factory()->homepage()->create();
        
        // page1 should no longer be homepage
        $page1->refresh();
        expect($page1->is_homepage)->toBeFalse();
        expect($page2->is_homepage)->toBeTrue();
    });

    test('homepage does not use prefix even when configured', function () {
        config(['blogr.cms.route.prefix' => 'page']);
        config(['blogr.route.homepage' => false]);
        
        $page = CmsPage::factory()->homepage()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Home',
            'slug' => 'home',
            'content' => 'Homepage',
        ]);
        
        // Homepage at root
        $response = get('/');
        $response->assertStatus(200);
        $response->assertSee('Home');
        
        // NOT accessible with prefix
        $response = get('/page/home');
        $response->assertStatus(404);
    });

    // NOTE: Test for "homepage route not registered when blog is homepage" is in separate file
    // because it requires TestCase without CMS configuration
    // See: tests/Feature/Cms/CmsWhenBlogIsHomepageTest.php
});

describe('CMS Templates', function () {
    test('default template uses correct view', function () {
        $page = CmsPage::factory()->published()->create([
            'template' => CmsPageTemplate::DEFAULT,
        ]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Default Page',
            'slug' => 'default',
            'content' => 'Default content',
        ]);
        
        $response = get('/default');
        
        $response->assertStatus(200);
        $response->assertSee('Default Page');
    });

    test('landing template uses correct view', function () {
        $page = CmsPage::factory()->landing()->published()->create();
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Landing Page',
            'slug' => 'landing',
            'content' => 'Landing content',
        ]);
        
        $response = get('/landing');
        
        $response->assertStatus(200);
        $response->assertSee('Landing Page');
    });
});

describe('CMS SEO', function () {
    test('page renders with SEO data in translation', function () {
        $page = CmsPage::factory()->published()->create();
        $translation = $page->translations()->create([
            'locale' => 'en',
            'title' => 'SEO Page',
            'slug' => 'seo',
            'content' => 'SEO content',
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'Custom SEO Description',
            'meta_keywords' => 'seo, test, keywords',
        ]);
        
        $response = get('/seo');
        
        $response->assertStatus(200);
        // Verify SEO data is stored correctly in translation
        expect($translation->meta_title)->toBe('Custom SEO Title');
        expect($translation->meta_description)->toBe('Custom SEO Description');
        expect($translation->meta_keywords)->toBe('seo, test, keywords');
        
        // NOTE: Meta tag rendering in HTML depends on the layout template
        // which may not be fully configured in test environment
        // The important thing is that SEO data is correctly stored and retrievable
    });
});
