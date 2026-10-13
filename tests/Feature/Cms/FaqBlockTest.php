<?php

use Illuminate\Support\Facades\View;

uses(Happytodev\Blogr\Tests\CmsTestCase::class);
uses()->group('cms', 'blocks');

/**
 * Issue #351 — the FAQ accordion prints the bundle key inside Alpine expressions.
 * On an edited page that key is a UUID, and the expression stops being valid
 * JavaScript: no question opens, and the console fills with errors.
 */
test('regression_351_faq_accordion_uses_the_loop_index_not_the_key', function () {
    $data = [
        'items' => [
            'b840a197-7fe3-46f2-849b-f17d6e9f3d17' => ['question' => 'First question?', 'answer' => 'First answer.'],
            '18da9113-0254-4756-9e47-b531e8cf86e7' => ['question' => 'Second question?', 'answer' => 'Second answer.'],
        ],
    ];

    $html = View::make('blogr::components.blocks.faq', ['data' => $data])->render();

    expect($html)
        ->toContain('First question?')
        ->toContain('Second question?')
        ->not->toMatch('/activeItem (?:===|!==) [0-9a-f]{8}-/')
        ->toContain('activeItem = activeItem === 0 ? null : 0')
        ->toContain('activeItem = activeItem === 1 ? null : 1');
});
