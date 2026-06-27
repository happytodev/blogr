<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\View;

uses(CmsTestCase::class);

beforeEach(function () {
    $this->page = CmsPage::create([
        'slug' => 'commissions-test',
        'template' => 'landing',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('pricing commissions block stores commission items', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Commissions',
        'slug' => 'commissions',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'pricing_commissions',
                'data' => [
                    'heading' => 'My Commissions',
                    'description' => 'Commission types available',
                    'items' => [
                        [
                            'image' => 'cms-blocks/comm1.jpg',
                            'title' => 'Portrait',
                            'description' => 'A digital portrait',
                            'price' => '50€',
                            'status' => 'open',
                        ],
                        [
                            'image' => 'cms-blocks/comm2.jpg',
                            'title' => 'Landscape',
                            'description' => 'A scenic landscape',
                            'price' => '100€',
                            'status' => 'on_request',
                        ],
                    ],
                    'layout' => 'grid',
                ],
            ],
        ],
    ]);

    $blocks = $translation->blocks;

    expect($blocks[0]['type'])->toBe('pricing_commissions');
    expect($blocks[0]['data']['items'])->toHaveCount(2);
    expect($blocks[0]['data']['items'][0]['title'])->toBe('Portrait');
    expect($blocks[0]['data']['items'][0]['status'])->toBe('open');
});

test('pricing commissions renders all items with prices and status', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Commissions',
        'slug' => 'commissions-render',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'pricing_commissions',
                'data' => [
                    'heading' => 'My Commissions',
                    'description' => 'Check out my work',
                    'items' => [
                        [
                            'image' => 'cms-blocks/comm1.jpg',
                            'title' => 'Portrait',
                            'description' => 'Digital portrait',
                            'price' => '50€',
                            'status' => 'open',
                        ],
                        [
                            'image' => 'cms-blocks/comm2.jpg',
                            'title' => 'Landscape',
                            'description' => 'Scenic landscape',
                            'price' => '100€',
                            'status' => 'closed',
                        ],
                        [
                            'image' => 'cms-blocks/comm3.jpg',
                            'title' => 'Sketch',
                            'description' => 'Quick sketch',
                            'price' => 'Sur devis',
                            'status' => 'on_request',
                        ],
                    ],
                    'layout' => 'grid',
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.pricing_commissions', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('My Commissions')
        ->toContain('Portrait')
        ->toContain('50€')
        ->toContain('Landscape')
        ->toContain('100€')
        ->toContain('Sketch')
        ->toContain('Sur devis')
        ->toContain('Digital portrait')
        ->toContain('Open')
        ->toContain('Closed')
        ->toContain('On Request');
});

test('pricing commissions renders status badges correctly', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Status Test',
        'slug' => 'status-test',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'pricing_commissions',
                'data' => [
                    'items' => [
                        [
                            'image' => 'cms-blocks/comm1.jpg',
                            'title' => 'Portrait',
                            'price' => '50€',
                            'status' => 'open',
                        ],
                        [
                            'image' => 'cms-blocks/comm2.jpg',
                            'title' => 'Landscape',
                            'price' => '100€',
                            'status' => 'closed',
                        ],
                        [
                            'image' => 'cms-blocks/comm3.jpg',
                            'title' => 'Sketch',
                            'price' => 'Sur devis',
                            'status' => 'on_request',
                        ],
                    ],
                    'layout' => 'carousel',
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.pricing_commissions', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('bg-green-500')
        ->toContain('bg-red-500')
        ->toContain('bg-blue-500');
});

test('pricing commissions handles image as array from Filament upload', function () {
    $data = [
        'items' => [
            ['image' => ['cms-blocks/comm1.jpg'], 'title' => 'Item 1', 'price' => '30€', 'status' => 'open'],
            ['image' => ['cms-blocks/comm2.jpg'], 'title' => 'Item 2', 'price' => '40€', 'status' => 'open'],
        ],
        'layout' => 'grid',
    ];

    $html = View::make('blogr::components.blocks.pricing_commissions', ['data' => $data])->render();

    expect($html)
        ->toContain('storage/cms-blocks/comm1.jpg')
        ->toContain('storage/cms-blocks/comm2.jpg')
        ->not->toContain('Array to string conversion');
});

test('pricing commissions renders carousel layout', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Carousel',
        'slug' => 'carousel-comm',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'pricing_commissions',
                'data' => [
                    'items' => [
                        ['image' => 'cms-blocks/comm1.jpg', 'title' => 'Item 1', 'price' => '30€', 'status' => 'open'],
                        ['image' => 'cms-blocks/comm2.jpg', 'title' => 'Item 2', 'price' => '40€', 'status' => 'open'],
                        ['image' => 'cms-blocks/comm3.jpg', 'title' => 'Item 3', 'price' => '50€', 'status' => 'open'],
                    ],
                    'layout' => 'carousel',
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.pricing_commissions', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('x-data')
        ->toContain('overflow-x-auto')
        ->toContain('snap-x');
});
