<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('block with background_type none renders no custom background', function () {
    $data = [
        'background_type' => 'none',
        'content' => 'Test content',
    ];

    $html = View::make('blogr::components.blocks.content', ['data' => $data])->render();

    expect($html)
        ->toContain('Test content')
        ->not->toContain('background-color:');
});

test('block with background_type_dark none renders no custom background in dark mode', function () {
    $data = [
        'background_type' => 'none',
        'background_type_dark' => 'none',
        'content' => 'Test content',
    ];

    $html = View::make('blogr::components.blocks.content', ['data' => $data])->render();

    expect($html)
        ->toContain('Test content')
        ->not->toContain('background-color:');
});
