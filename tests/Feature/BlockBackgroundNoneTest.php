<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('block with background_type_dark none renders theme dark background in dark mode', function () {
    $data = [
        'background_type' => 'none',
        'background_type_dark' => 'none',
        'content' => 'Test content',
    ];

    $html = View::make('blogr::components.blocks.content', ['data' => $data])->render();

    expect($html)
        ->toContain('var(--color-bg-dark)')
        ->toContain('Test content');
});

test('block with background_type none renders theme light background in light mode', function () {
    $data = [
        'background_type' => 'none',
        'content' => 'Test content',
    ];

    $html = View::make('blogr::components.blocks.content', ['data' => $data])->render();

    expect($html)
        ->toContain('var(--color-bg)')
        ->toContain('Test content');
});
