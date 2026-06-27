<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\View;

uses(CmsTestCase::class);

beforeEach(function () {
    $this->page = CmsPage::create([
        'slug' => 'gallery-test',
        'template' => 'landing',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('gallery defaults to grid when no display_mode', function () {
    $blocks = [
        [
            'type' => 'gallery',
            'data' => [
                'heading' => 'Gallery',
                'images' => ['cms-blocks/img1.jpg', 'cms-blocks/img2.jpg'],
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Gallery Test',
        'slug' => 'gallery-test',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.gallery', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('Gallery')
        ->toContain('grid');
});

test('gallery renders horizontal mode', function () {
    $blocks = [
        [
            'type' => 'gallery',
            'data' => [
                'heading' => 'Horizontal Gallery',
                'images' => ['cms-blocks/img1.jpg', 'cms-blocks/img2.jpg', 'cms-blocks/img3.jpg'],
                'display_mode' => 'horizontal',
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Horizontal Test',
        'slug' => 'horizontal-test',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.gallery', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('Horizontal Gallery')
        ->toContain('overflow-x-auto')
        ->toContain('snap-x');
});

test('gallery renders filtered mode with categories', function () {
    $blocks = [
        [
            'type' => 'gallery',
            'data' => [
                'heading' => 'Filtered Gallery',
                'images' => ['cms-blocks/img1.jpg', 'cms-blocks/img2.jpg', 'cms-blocks/img3.jpg'],
                'display_mode' => 'filtered',
                'categories' => ['Portraits', 'Landscapes', 'Sketches'],
                'image_categories' => ['0' => 'Portraits', '1' => 'Landscapes', '2' => 'Portraits'],
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Filtered Test',
        'slug' => 'filtered-test',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.gallery', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('Filtered Gallery')
        ->toContain('Portraits')
        ->toContain('Landscapes');
});

test('gallery renders B/W hover effect when enabled', function () {
    $blocks = [
        [
            'type' => 'gallery',
            'data' => [
                'heading' => 'B/W Gallery',
                'images' => ['cms-blocks/img1.jpg'],
                'display_mode' => 'horizontal',
                'bw_hover' => true,
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'BW Test',
        'slug' => 'bw-test',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.gallery', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('grayscale');
});

test('gallery preserves backward compatibility with old gallery data without display_mode', function () {
    $blocks = [
        [
            'type' => 'gallery',
            'data' => [
                'heading' => 'Old Gallery',
                'description' => 'Old desc',
                'images' => ['cms-blocks/img1.jpg', 'cms-blocks/img2.jpg'],
                'layout' => 'grid',
                'columns' => '3',
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Old Gallery Test',
        'slug' => 'old-gallery-test',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.gallery', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('Old Gallery')
        ->toContain('Old desc')
        ->toContain('lg:grid-cols-3');
});

test('gallery filtered mode initializes with Alpine x-data', function () {
    $blocks = [
        [
            'type' => 'gallery',
            'data' => [
                'heading' => 'Filtered',
                'images' => ['cms-blocks/img1.jpg', 'cms-blocks/img2.jpg'],
                'display_mode' => 'filtered',
                'categories' => ['Portraits', 'Landscapes'],
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Alpine Filter Test',
        'slug' => 'alpine-filter-test',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.gallery', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('x-data')
        ->toContain('activeFilter');
});
