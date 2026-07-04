<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
});

test('feature_carousel_has_pause_button', function () {
    $data = [
        'slides' => [
            ['image' => 'carousel/slide1.jpg', 'title' => 'Slide 1'],
            ['image' => 'carousel/slide2.jpg', 'title' => 'Slide 2'],
        ],
        'show_arrows' => true,
        'show_dots' => true,
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('togglePause');
});

test('feature_carousel_respects_prefers_reduced_motion', function () {
    $data = [
        'slides' => [
            ['image' => 'carousel/slide1.jpg', 'title' => 'Slide 1'],
            ['image' => 'carousel/slide2.jpg', 'title' => 'Slide 2'],
        ],
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('prefers-reduced-motion');
});

test('feature_carousel_pause_button_has_aria_label', function () {
    $data = [
        'slides' => [
            ['image' => 'carousel/slide1.jpg', 'title' => 'Slide 1'],
            ['image' => 'carousel/slide2.jpg', 'title' => 'Slide 2'],
        ],
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('aria-label="Toggle autoplay"');
});

test('feature_carousel_has_pause_state', function () {
    $data = [
        'slides' => [
            ['image' => 'carousel/slide1.jpg', 'title' => 'Slide 1'],
            ['image' => 'carousel/slide2.jpg', 'title' => 'Slide 2'],
        ],
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('isPaused');
});
