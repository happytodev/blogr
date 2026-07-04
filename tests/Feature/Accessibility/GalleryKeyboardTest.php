<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('feature_gallery_grid_images_are_keyboard_accessible', function () {
    $data = [
        'heading' => 'My Gallery',
        'layout' => 'grid',
        'display_mode' => 'grid',
        'images' => ['gallery/photo1.jpg', 'gallery/photo2.jpg'],
    ];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    expect($html)
        ->toContain('tabindex="0"')
        ->toContain('role="button"')
        ->toContain('@keydown.enter')
        ->toContain('@keydown.space');
});

test('feature_gallery_horizontal_images_are_keyboard_accessible', function () {
    $data = [
        'heading' => 'My Gallery',
        'display_mode' => 'horizontal',
        'images' => ['gallery/photo1.jpg', 'gallery/photo2.jpg'],
    ];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    expect($html)
        ->toContain('tabindex="0"')
        ->toContain('role="button"');
});

test('feature_gallery_masonry_images_are_keyboard_accessible', function () {
    $data = [
        'heading' => 'My Gallery',
        'layout' => 'masonry',
        'display_mode' => 'grid',
        'images' => ['gallery/photo1.jpg', 'gallery/photo2.jpg', 'gallery/photo3.jpg'],
    ];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    expect($html)
        ->toContain('tabindex="0"')
        ->toContain('role="button"');
});

test('feature_gallery_bento_images_are_keyboard_accessible', function () {
    $data = [
        'heading' => 'My Gallery',
        'layout' => 'bento',
        'display_mode' => 'grid',
        'images' => ['gallery/photo1.jpg', 'gallery/photo2.jpg', 'gallery/photo3.jpg', 'gallery/photo4.jpg'],
    ];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    expect($html)
        ->toContain('tabindex="0"')
        ->toContain('role="button"');
});

test('feature_gallery_lightbox_buttons_have_aria_labels', function () {
    $data = [
        'heading' => 'My Gallery',
        'layout' => 'grid',
        'display_mode' => 'grid',
        'images' => ['gallery/photo1.jpg'],
    ];

    $html = View::make('blogr::components.blocks.gallery', ['data' => $data])->render();

    expect($html)
        ->toContain('aria-label="Close lightbox"')
        ->toContain('aria-label="Previous image"')
        ->toContain('aria-label="Next image"');
});
