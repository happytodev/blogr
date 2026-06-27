<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\Storage;

uses(CmsTestCase::class);

beforeEach(function () {
    Storage::fake('public');

    $this->page = CmsPage::create([
        'slug' => 'carousel-integration',
        'template' => 'custom',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('carousel block data is stored and retrieved correctly', function () {
    $blocks = [
        [
            'type' => 'carousel',
            'data' => [
                'slides' => [
                    [
                        'image' => 'cms-blocks/carousel/slide1.jpg',
                        'title' => 'First Slide',
                        'subtitle' => 'First subtitle',
                        'cta_text' => 'Learn More',
                        'cta_url' => 'https://example.com',
                    ],
                    [
                        'image' => 'cms-blocks/carousel/slide2.jpg',
                        'title' => 'Second Slide',
                        'subtitle' => '',
                        'cta_text' => '',
                        'cta_url' => '',
                    ],
                ],
                'height' => 'md',
                'autoplay_speed' => 5000,
                'show_arrows' => true,
                'show_dots' => true,
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Carousel Integration',
        'slug' => 'carousel-integration',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks[0]['type'])->toBe('carousel');
    expect($translation->blocks[0]['data']['slides'][0]['image'])->toBe('cms-blocks/carousel/slide1.jpg');
    expect($translation->blocks[0]['data']['slides'][1]['image'])->toBe('cms-blocks/carousel/slide2.jpg');
});

test('carousel block persists to database and retains image paths', function () {
    $blocks = [
        [
            'type' => 'carousel',
            'data' => [
                'slides' => [
                    [
                        'image' => 'cms-blocks/carousel/slide1.jpg',
                        'title' => 'First Slide',
                    ],
                ],
                'height' => 'md',
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Carousel Persist',
        'slug' => 'carousel-persist',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    // Simulate what Eloquent does: read back from "database"
    $fresh = CmsPageTranslation::find($translation->id);

    expect($fresh->blocks[0]['data']['slides'][0]['image'])->toBe('cms-blocks/carousel/slide1.jpg');
    expect($fresh->blocks[0]['data']['slides'][0]['title'])->toBe('First Slide');
    expect($fresh->blocks[0]['data']['slides'][0])->toHaveKey('image');
    expect($fresh->blocks[0]['data']['slides'][0]['image'])->not->toBeEmpty();
    expect($fresh->blocks[0]['data']['slides'][0]['image'])->not->toBeNull();
});

test('carousel block renders stored images on frontend', function () {
    Storage::disk('public')->put('cms-blocks/carousel/slide1.jpg', 'fake-image-content');
    Storage::disk('public')->put('cms-blocks/carousel/slide2.jpg', 'fake-image-content');

    $blocks = [
        [
            'type' => 'carousel',
            'data' => [
                'slides' => [
                    ['image' => 'cms-blocks/carousel/slide1.jpg', 'title' => 'First'],
                    ['image' => 'cms-blocks/carousel/slide2.jpg', 'title' => 'Second'],
                ],
                'height' => 'md',
                'autoplay_speed' => 5000,
                'show_arrows' => true,
                'show_dots' => true,
            ],
        ],
    ];

    // Store as JSON (same as Eloquent array cast)
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Carousel Render',
        'slug' => 'carousel-render',
        'content' => 'Test',
        'blocks' => json_decode(json_encode($blocks), true),
    ]);

    $html = view('blogr::components.blocks.carousel', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('First')
        ->toContain('Second')
        ->toContain('/storage/cms-blocks/carousel/slide1.jpg')
        ->toContain('/storage/cms-blocks/carousel/slide2.jpg')
        ->toContain('x-data')
        ->toContain('goTo(0)')
        ->toContain('goTo(1)');
});

test('carousel simulates Filament form submission data format', function () {
    $uuid1 = '3c5b8554-ad6a-49fe-94c6-791432c42743';
    $uuid2 = '172e9085-71a1-4a21-b28c-f652d7bb2928';

    // This is the exact format Filament's Builder + Repeater submits
    $blocks = [
        [
            'type' => 'carousel',
            'data' => [
                'slides' => [
                    $uuid1 => [
                        'image' => 'cms-blocks/carousel/slide1.jpg',
                        'title' => 'First',
                        'subtitle' => '',
                        'cta_text' => '',
                        'cta_url' => '',
                    ],
                    $uuid2 => [
                        'image' => 'cms-blocks/carousel/slide2.jpg',
                        'title' => 'Second',
                        'subtitle' => '',
                        'cta_text' => '',
                        'cta_url' => '',
                    ],
                ],
                'height' => 'md',
                'autoplay_speed' => 5000,
                'show_arrows' => true,
                'show_dots' => true,
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Carousel Form Sim',
        'slug' => 'carousel-form-sim',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $fresh = CmsPageTranslation::find($translation->id);

    // Even with UUID keys, the image paths must be preserved
    $slides = $fresh->blocks[0]['data']['slides'] ?? [];
    $imagePaths = array_map(fn ($s) => $s['image'] ?? '', $slides);

    expect($imagePaths)->toContain('cms-blocks/carousel/slide1.jpg');
    expect($imagePaths)->toContain('cms-blocks/carousel/slide2.jpg');

    // Rendering must also work
    $html = view('blogr::components.blocks.carousel', ['data' => $fresh->blocks[0]['data']])->render();

    expect($html)
        ->toContain('/storage/cms-blocks/carousel/slide1.jpg')
        ->toContain('/storage/cms-blocks/carousel/slide2.jpg');
});
