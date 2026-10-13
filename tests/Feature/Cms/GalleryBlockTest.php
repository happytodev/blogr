<?php

use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\View;

uses(CmsTestCase::class);
uses()->group('cms', 'blocks');

/**
 * Issue #350 — the gallery lightbox opens on an empty overlay when the stored
 * images carry UUID keys (the content editor stores lists that way).
 */
test('regression_350_gallery_sends_a_list_to_alpine_with_uuid_keyed_images', function () {
    $data = [
        'images' => [
            'b840a197-7fe3-46f2-849b-f17d6e9f3d17' => 'cms-blocks/gallery/first.png',
            '18da9113-0254-4756-9e47-b531e8cf86e7' => 'cms-blocks/gallery/second.png',
        ],
    ];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    // A JSON ARRAY, never an object: an object has no .length (the counter renders
    // "2 /" with no total) and images[0] is undefined (the lightbox shows nothing).
    expect($html)
        ->toContain('first.png')
        ->toContain('second.png')
        ->not->toMatch('/images:\s*\{/')
        ->not->toContain('Invalid or unexpected token');
});

test('regression_350_gallery_lightbox_image_is_sized_against_the_window', function () {
    $data = ['images' => ['cms-blocks/gallery/only.png']];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    // Percentage of a parent that takes its width from its content resolves to
    // zero — the image loaded and painted nothing. The view must not rely on it,
    // and the overlay must sit above the template's z-50 back-to-top button.
    expect($html)
        ->not->toContain('max-w-full max-h-full object-contain')
        ->toContain('max-w-[90vw]')
        ->toContain('z-[60]');
});
