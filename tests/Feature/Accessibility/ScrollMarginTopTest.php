<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
    config(['blogr.cms.enabled' => true]);
    config(['blogr.locales.enabled' => false]);
    config(['blogr.ui.navigation.enabled' => true]);

    view()->share('seoData', [
        'title' => 'Test Blog',
        'description' => 'A test blog',
    ]);
    view()->share('currentLocale', 'en');
});

test('feature_main_content_has_scroll_margin_top', function () {
    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('scroll-margin-top');
});
