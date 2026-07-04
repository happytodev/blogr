<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
});

test('feature_carousel_arrow_buttons_have_aria_labels', function () {
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
        ->toContain('aria-label="Previous slide"')
        ->toContain('aria-label="Next slide"');
});

test('feature_carousel_dot_navigation_has_aria_labels', function () {
    $data = [
        'slides' => [
            ['image' => 'carousel/slide1.jpg', 'title' => 'Slide 1'],
            ['image' => 'carousel/slide2.jpg', 'title' => 'Slide 2'],
            ['image' => 'carousel/slide3.jpg', 'title' => 'Slide 3'],
        ],
        'show_arrows' => true,
        'show_dots' => true,
    ];

    $html = View::make('blogr::components.blocks.carousel', ['data' => $data])->render();

    expect($html)
        ->toContain('aria-label="Go to slide 1"')
        ->toContain('aria-label="Go to slide 2"')
        ->toContain('aria-label="Go to slide 3"');
});

test('feature_carousel_supports_keyboard_navigation', function () {
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
        ->toContain('@keydown.arrow-right')
        ->toContain('@keydown.arrow-left');
});

test('feature_carousel_pauses_on_keyboard_focus', function () {
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
        ->toContain('@focusin')
        ->toContain('@focusout')
        ->toContain('stopAutoplay')
        ->toContain('startAutoplay');
});
