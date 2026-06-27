<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\View;

uses(CmsTestCase::class);

beforeEach(function () {
    $this->page = CmsPage::create([
        'slug' => 'artist-test',
        'template' => 'landing',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('artist bio block stores data correctly', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Artist Page',
        'slug' => 'artist',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'artist_bio',
                'data' => [
                    'avatar' => 'cms-blocks/artist/avatar.jpg',
                    'title' => 'Jane Doe',
                    'bio' => 'Digital illustrator based in Paris.',
                    'layout' => 'left',
                    'social_links' => [
                        ['platform' => 'twitter', 'url' => 'https://twitter.com/janedoe'],
                        ['platform' => 'instagram', 'url' => 'https://instagram.com/janedoe'],
                    ],
                ],
            ],
        ],
    ]);

    $blocks = $translation->blocks;

    expect($blocks[0]['type'])->toBe('artist_bio');
    expect($blocks[0]['data']['title'])->toBe('Jane Doe');
    expect($blocks[0]['data']['bio'])->toBe('Digital illustrator based in Paris.');
    expect($blocks[0]['data']['social_links'])->toHaveCount(2);
});

test('artist bio renders avatar, title, and bio', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Artist',
        'slug' => 'artist-render',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'artist_bio',
                'data' => [
                    'avatar' => 'cms-blocks/artist/avatar.jpg',
                    'title' => 'Jane Doe',
                    'bio' => 'Digital illustrator based in Paris.',
                    'layout' => 'left',
                    'social_links' => [
                        ['platform' => 'twitter', 'url' => 'https://twitter.com/janedoe'],
                    ],
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.artist_bio', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('Jane Doe')
        ->toContain('Digital illustrator based in Paris.')
        ->toContain('https://twitter.com/janedoe');
});

test('artist bio renders centered layout', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Artist Center',
        'slug' => 'artist-center',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'artist_bio',
                'data' => [
                    'title' => 'Jane Doe',
                    'bio' => 'Bio text',
                    'layout' => 'center',
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.artist_bio', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('text-center')
        ->toContain('mx-auto');
});

test('artist bio renders left layout', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Artist Left',
        'slug' => 'artist-left',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'artist_bio',
                'data' => [
                    'title' => 'Jane Doe',
                    'bio' => 'Bio text',
                    'layout' => 'left',
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.artist_bio', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('md:flex-row');
});

test('artist bio renders social links via social-links component', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'Artist Social',
        'slug' => 'artist-social',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'artist_bio',
                'data' => [
                    'title' => 'Jane Doe',
                    'bio' => 'Bio',
                    'social_links' => [
                        ['platform' => 'instagram', 'url' => 'https://instagram.com/janedoe'],
                        ['platform' => 'bluesky', 'url' => 'https://bsky.app/profile/janedoe'],
                    ],
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.artist_bio', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('https://instagram.com/janedoe')
        ->toContain('https://bsky.app/profile/janedoe');
});

test('artist bio renders without optional avatar', function () {
    $translation = $this->page->translations()->create([
        'locale' => 'en',
        'title' => 'No Avatar',
        'slug' => 'no-avatar',
        'content' => 'Test',
        'blocks' => [
            [
                'type' => 'artist_bio',
                'data' => [
                    'title' => 'Jane Doe',
                    'bio' => 'Bio text',
                ],
            ],
        ],
    ]);

    $html = View::make('blogr::components.blocks.artist_bio', ['data' => $translation->blocks[0]['data']])->render();

    expect($html)
        ->toContain('Jane Doe')
        ->toContain('Bio text');
});

test('artist bio handles avatar as array from Filament upload', function () {
    $data = [
        'avatar' => ['cms-blocks/artist/avatar.jpg'],
        'title' => 'Jane Doe',
        'bio' => 'Bio',
    ];

    $html = View::make('blogr::components.blocks.artist_bio', ['data' => $data])->render();

    expect($html)
        ->toContain('storage/cms-blocks/artist/avatar.jpg')
        ->not->toContain('Array to string conversion');
});
