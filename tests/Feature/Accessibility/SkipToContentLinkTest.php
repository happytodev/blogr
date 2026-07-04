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
});

test('feature_skip_to_content_link_is_first_focusable_element', function () {
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    // Find the position of the skip link and the nav element
    $skipPos = strpos($html, 'href="#main-content"');
    $navPos = strpos($html, '<nav');

    expect($skipPos)->not->toBeFalse('Skip link not found in layout')
        ->and($navPos)->not->toBeFalse('<nav> element not found')
        ->and($skipPos)->toBeLessThan($navPos, 'Skip link must appear before <nav> in DOM order');
});

test('feature_skip_to_content_link_has_visible_focus_state', function () {
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('href="#main-content"')
        ->toContain('Skip to main content')
        ->toContain('sr-only')
        ->toContain('focus:not-sr-only');
});

test('feature_skip_to_content_link_target_exists', function () {
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('id="main-content"');
});
