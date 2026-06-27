<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\View;

uses(CmsTestCase::class);

beforeEach(function () {
    $this->page = CmsPage::create([
        'slug' => 'carousel-test',
        'template' => 'landing',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Carousel Test',
        'slug' => 'carousel-test',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'carousel',
                'data' => [
                    'slides' => [
                        [
                            'image' => 'cms-blocks/slide1.jpg',
                            'title' => 'First Slide',
                            'subtitle' => 'First subtitle',
                            'cta_text' => 'Learn More',
                            'cta_url' => 'https://example.com',
                        ],
                        [
                            'image' => 'cms-blocks/slide2.jpg',
                            'title' => 'Second Slide',
                            'subtitle' => '',
                            'cta_text' => '',
                            'cta_url' => '',
                        ],
                    ],
                    'autoplay_speed' => 5000,
                    'show_arrows' => true,
                    'show_dots' => true,
                    'height' => 'md',
                ],
            ],
        ],
    ]);
});

test('carousel block is stored correctly', function () {
    $blocks = $this->translation->blocks;

    expect($blocks)->toBeArray();
    expect($blocks[0]['type'])->toBe('carousel');
    expect($blocks[0]['data']['slides'])->toHaveCount(2);
    expect($blocks[0]['data']['slides'][0]['title'])->toBe('First Slide');
});

test('carousel renders all slides', function () {
    $data = $this->translation->blocks[0]['data'];
    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('First Slide')
        ->toContain('First subtitle')
        ->toContain('Second Slide')
        ->toContain('Learn More')
        ->toContain('https://example.com');
});

test('carousel renders Alpine.js data attributes', function () {
    $data = $this->translation->blocks[0]['data'];
    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('x-data')
        ->toContain('x-transition');
});

test('carousel renders navigation arrows and dots', function () {
    $data = $this->translation->blocks[0]['data'];
    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('carousel-prev')
        ->toContain('carousel-next');
});

test('carousel renders without optional fields', function () {
    $blocks = [
        [
            'type' => 'carousel',
            'data' => [
                'slides' => [
                    ['image' => 'cms-blocks/slide1.jpg'],
                    ['image' => 'cms-blocks/slide2.jpg'],
                ],
            ],
        ],
    ];

    $translation = $this->page->translations()->create([
        'locale' => 'fr',
        'title' => 'Test Carousel',
        'slug' => 'carousel-test-fr',
        'content' => 'Test',
        'blocks' => $blocks,
    ]);

    $html = View::make('blogr::components.blocks.carousel', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('cms-blocks/slide1.jpg')
        ->not->toContain('Learn More');
});

test('carousel handles image as array from Filament upload', function () {
    $data = [
        'slides' => [
            ['image' => ['cms-blocks/slide1.jpg']],
            ['image' => ['cms-blocks/slide2.jpg']],
        ],
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('storage/cms-blocks/slide1.jpg')
        ->toContain('storage/cms-blocks/slide2.jpg')
        ->not->toContain('Array to string conversion');
});

test('carousel renders with slides array having null entry after unset', function () {
    $slides = [['image' => 'img1.jpg'], null, ['image' => 'img3.jpg']];

    $data = ['slides' => $slides];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('img1.jpg')
        ->toContain('img3.jpg');
});

test('carousel first slide is visible without Alpine.js (no display:none)', function () {
    $data = [
        'slides' => [
            ['image' => 'cms-blocks/slide1.jpg', 'title' => 'First'],
            ['image' => 'cms-blocks/slide2.jpg', 'title' => 'Second'],
        ],
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    preg_match_all('/style="([^"]*)"/', $html, $matches);
    $firstStyle = $matches[1][0] ?? '';

    expect($firstStyle)->not->toContain('display: none');
});

test('carousel handles UUID keys from Filament Repeater', function () {
    $uuid1 = '3c5b8554-ad6a-49fe-94c6-791432c42743';
    $uuid2 = '172e9085-71a1-4a21-b28c-f652d7bb2928';

    $data = [
        'slides' => [
            $uuid1 => ['image' => 'cms-blocks/slide1.jpg', 'title' => 'First'],
            $uuid2 => ['image' => 'cms-blocks/slide2.jpg', 'title' => 'Second'],
        ],
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('First')
        ->toContain('Second')
        ->toContain('goTo(0)')
        ->toContain('goTo(1)')
        ->not->toContain('Invalid or unexpected token');
});

test('carousel block exists in the blocks list', function () {
    $blocks = $this->translation->blocks;

    expect($blocks[0])->toHaveKey('type');
    expect($blocks[0])->toHaveKey('data');
    expect($blocks[0]['data'])->toHaveKey('slides');
});
