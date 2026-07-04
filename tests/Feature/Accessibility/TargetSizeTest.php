<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('feature_social_links_have_minimum_target_size', function () {
    config(['blogr.ui.footer.social_links' => [
        'twitter' => 'https://twitter.com/test',
        'github' => 'https://github.com/test',
    ]]);

    $html = View::make('blogr::components.social-links', [
        'links' => config('blogr.ui.footer.social_links'),
    ])->render();

    expect($html)
        ->toContain('min-w-')
        ->toContain('min-h-');
});
