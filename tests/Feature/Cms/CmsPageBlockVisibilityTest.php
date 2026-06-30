<?php

use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Filament\Resources\CmsPages\CmsBlockBuilder;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsTestCase;

uses(CmsTestCase::class)->group('cms');

test('visible block is rendered in blocks renderer', function () {
    $html = view('blogr::components.blocks-renderer', [
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Hello World']],
        ],
    ])->render();

    expect($html)->toContain('Hello World');
});

test('hidden block is not rendered in blocks renderer', function () {
    $html = view('blogr::components.blocks-renderer', [
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Visible Block']],
            ['type' => 'hero', 'data' => ['title' => 'Hidden Block', 'hidden' => true]],
        ],
    ])->render();

    expect($html)->toContain('Visible Block');
    expect($html)->not->toContain('Hidden Block');
});

test('multiple hidden blocks are all excluded from render', function () {
    $html = view('blogr::components.blocks-renderer', [
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'First Visible']],
            ['type' => 'hero', 'data' => ['title' => 'Hidden One', 'hidden' => true]],
            ['type' => 'hero', 'data' => ['title' => 'Second Visible']],
            ['type' => 'hero', 'data' => ['title' => 'Hidden Two', 'hidden' => true]],
            ['type' => 'hero', 'data' => ['title' => 'Third Visible']],
        ],
    ])->render();

    expect($html)->toContain('First Visible');
    expect($html)->not->toContain('Hidden One');
    expect($html)->toContain('Second Visible');
    expect($html)->not->toContain('Hidden Two');
    expect($html)->toContain('Third Visible');
});

test('blocks without hidden key are rendered normally', function () {
    $html = view('blogr::components.blocks-renderer', [
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'No Hidden Key']],
            ['type' => 'hero', 'data' => ['title' => 'Hidden False', 'hidden' => false]],
        ],
    ])->render();

    expect($html)->toContain('No Hidden Key');
    expect($html)->toContain('Hidden False');
});

test('block with invalid URL in data renders without validation crash', function () {
    $html = view('blogr::components.blocks-renderer', [
        'blocks' => [
            ['type' => 'hero', 'data' => [
                'title' => 'Invalid URL Test',
                'cta_url' => 'not-a-valid-url',
            ]],
            ['type' => 'hero', 'data' => [
                'title' => 'Valid Block',
                'hidden' => true,
            ]],
        ],
    ])->render();

    expect($html)->toContain('Invalid URL Test');
    expect($html)->not->toContain('Valid Block');
});

test('block with null link_type in data renders without crash', function () {
    $html = view('blogr::components.blocks-renderer', [
        'blocks' => [
            ['type' => 'hero', 'data' => [
                'title' => 'Null Link Test',
                'cta_link_type' => null,
            ]],
        ],
    ])->render();

    expect($html)->toContain('Null Link Test');
});

test('isPluginLinkType handles null safely', function () {
    $result = CmsBlockBuilder::isPluginLinkType(null);

    expect($result)->toBeFalse();
});

test('hidden block on a rendered CMS page does not appear in HTML', function () {
    $page = CmsPage::create([
        'slug' => 'visibility-test',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $page->translations()->create([
        'locale' => 'en',
        'title' => 'Visibility Test',
        'slug' => 'visibility-test',
        'content' => '',
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Shown Block']],
            ['type' => 'hero', 'data' => ['title' => 'Hidden Block', 'hidden' => true]],
        ],
    ]);

    $response = $this->get('/visibility-test');

    $response->assertStatus(200);
    $response->assertSee('Shown Block');
    $response->assertDontSee('Hidden Block');
});
