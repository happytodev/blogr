<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Services\LocaleService;

// ─── auto_detect disabled (default) ───────────────────────────────────────────

it('returns config locales when auto_detect is disabled', function () {
    config()->set('blogr.locales.auto_detect', false);
    config()->set('blogr.locales.available', ['en', 'fr']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

// ─── auto_detect enabled with published content ───────────────────────────────

it('detects locales from published blog posts', function () {
    $post = BlogPost::factory()->published()->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.available', ['en', 'fr', 'es', 'de']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

it('ignores unpublished blog posts', function () {
    BlogPost::factory()->create();

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.available', ['en', 'fr']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);
});

it('detects locales from published CMS pages', function () {
    $page = CmsPage::factory()->published()->create();
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'test-en',
        'title' => 'Test EN',
    ]);
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'slug' => 'test-fr',
        'title' => 'Test FR',
    ]);

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.available', ['en', 'fr', 'de']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

it('ignores unpublished CMS pages', function () {
    $page = CmsPage::factory()->unpublished()->create();
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'test-en',
        'title' => 'Test EN',
    ]);
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'slug' => 'test-fr',
        'title' => 'Test FR',
    ]);

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.available', ['en', 'fr']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);
});

it('merges unique locales from both blog posts and CMS pages', function () {
    $post = BlogPost::factory()->published()->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();

    $page = CmsPage::factory()->published()->create();
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'es',
        'slug' => 'test-es',
        'title' => 'Test ES',
    ]);

    config()->set('blogr.locales.auto_detect', true);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'es', 'fr']);
});

// ─── Sorted alphabetically ────────────────────────────────────────────────────

it('returns locales sorted alphabetically', function () {
    $post = BlogPost::factory()->published()->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('de')->create();

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.available', ['en', 'fr', 'de']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['de', 'en', 'fr']);
});

// ─── No published content → default locale fallback ───────────────────────────

it('returns default locale when auto_detect enabled but no published content', function () {
    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.default', 'en');

    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);
});

// ─── restrict option ──────────────────────────────────────────────────────────

it('filters detected locales with restrict option', function () {
    $post = BlogPost::factory()->published()->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('es')->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('de')->create();

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.restrict', ['en', 'fr']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

// ─── Cache invalidation ───────────────────────────────────────────────────────

it('flushes cache when a blog post translation is saved', function () {
    config()->set('blogr.locales.auto_detect', true);

    $post = BlogPost::factory()->published()->create();
    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);

    BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

it('flushes cache when a blog post translation is deleted', function () {
    config()->set('blogr.locales.auto_detect', true);

    $post = BlogPost::factory()->published()->create();
    $fr = BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);

    $fr->delete();

    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);
});

it('flushes cache when a CMS page translation is saved', function () {
    config()->set('blogr.locales.auto_detect', true);

    $page = CmsPage::factory()->published()->create();
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'test-en',
        'title' => 'Test EN',
    ]);
    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);

    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'slug' => 'test-fr',
        'title' => 'Test FR',
    ]);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

it('flushes cache when a CMS page translation is deleted', function () {
    config()->set('blogr.locales.auto_detect', true);

    $page = CmsPage::factory()->published()->create();
    CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'slug' => 'test-en',
        'title' => 'Test EN',
    ]);
    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'fr',
        'slug' => 'test-fr',
        'title' => 'Test FR',
    ]);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);

    $translation->delete();

    expect(app(LocaleService::class)->getAvailable())->toBe(['en']);
});

// ─── disabled locales filtering ─────────────────────────────────────────────────

it('filters out disabled locales from config list', function () {
    config()->set('blogr.locales.auto_detect', false);
    config()->set('blogr.locales.available', ['en', 'fr', 'de', 'es']);
    config()->set('blogr.locales.disabled', ['de', 'es']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

it('filters out disabled locales from auto-detected list', function () {
    $post = BlogPost::factory()->published()->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('fr')->create();
    BlogPostTranslation::factory()->forBlogPost($post)->locale('de')->create();

    config()->set('blogr.locales.auto_detect', true);
    config()->set('blogr.locales.disabled', ['de']);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr']);
});

it('returns all locales when disabled list is empty', function () {
    config()->set('blogr.locales.auto_detect', false);
    config()->set('blogr.locales.available', ['en', 'fr', 'de']);
    config()->set('blogr.locales.disabled', []);

    expect(app(LocaleService::class)->getAvailable())->toBe(['en', 'fr', 'de']);
});

it('returns empty when all locales are disabled', function () {
    config()->set('blogr.locales.auto_detect', false);
    config()->set('blogr.locales.available', ['en', 'fr']);
    config()->set('blogr.locales.disabled', ['en', 'fr']);

    expect(app(LocaleService::class)->getAvailable())->toBe([]);
});
