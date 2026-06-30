<?php

use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\URL;

uses(CmsTestCase::class);

test('preview with valid signed URL shows draft content for published page', function () {
    $page = CmsPage::create([
        'slug' => 'preview-test',
        'template' => CmsPageTemplate::DEFAULT,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Original Title',
        'slug' => 'preview-test',
        'content' => 'Original content',
        'blocks' => null,
    ]);

    app(VersioningService::class)->saveDraft($translation, [
        'title' => 'Draft Title',
        'content' => 'Draft content',
    ]);

    $url = URL::signedRoute('cms.page.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Draft Title');
    $response->assertSee('Draft content');
    $response->assertDontSee('Original Title');
    $response->assertDontSee('Original content');
    $response->assertSee('PREVIEW');
});

test('preview works for unpublished pages', function () {
    $page = CmsPage::create([
        'slug' => 'unpublished-preview',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => false,
        'published_at' => null,
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Unpublished Page',
        'slug' => 'unpublished-preview',
        'content' => 'Not yet published',
        'blocks' => [],
    ]);

    $url = URL::signedRoute('cms.page.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Unpublished Page');
});

test('preview with invalid signature returns 403', function () {
    $page = CmsPage::create([
        'slug' => 'invalid-sig',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Invalid Sig Page',
        'slug' => 'invalid-sig',
        'content' => '',
        'blocks' => [],
    ]);

    $response = $this->get('/_preview/cms/' . $translation->id . '?signature=invalid');

    $response->assertStatus(403);
});

test('preview for non-existent translation returns 404', function () {
    $url = URL::signedRoute('cms.page.preview', ['translationId' => 99999]);

    $response = $this->get($url);

    $response->assertStatus(404);
});

test('preview shows preview banner indicator', function () {
    $page = CmsPage::create([
        'slug' => 'preview-banner',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Banner Test',
        'slug' => 'preview-banner',
        'content' => 'Content',
        'blocks' => [],
    ]);

    $url = URL::signedRoute('cms.page.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSeeText('PREVIEW');
});

test('preview respects hidden blocks', function () {
    $page = CmsPage::create([
        'slug' => 'preview-hidden',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Preview Hidden Test',
        'slug' => 'preview-hidden',
        'content' => '',
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Visible In Preview', 'hidden' => false]],
            ['type' => 'hero', 'data' => ['title' => 'Hidden In Preview', 'hidden' => true]],
        ],
    ]);

    $url = URL::signedRoute('cms.page.preview', ['translationId' => $translation->id]);

    $response = $this->get($url);

    $response->assertStatus(200);
    $response->assertSee('Visible In Preview');
    $response->assertDontSee('Hidden In Preview');
});
