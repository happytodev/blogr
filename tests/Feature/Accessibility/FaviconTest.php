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

test('feature_favicon_renders_default_blogr_svg_when_not_configured', function () {
    config(['blogr.ui.favicon.path' => null]);
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('rel="icon"')
        ->toContain('type="image/svg+xml"')
        ->toContain('blogr-favicon.svg');
});

test('feature_favicon_renders_configured_favicon_path', function () {
    config(['blogr.ui.favicon.path' => 'blogr/favicon/favicon.png']);
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('rel="icon"')
        ->toContain('href="'.asset('storage/blogr/favicon/favicon.png').'"');
});

test('feature_favicon_renders_apple_touch_icon', function () {
    config(['blogr.ui.favicon.path' => 'blogr/favicon/favicon.png']);
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('rel="apple-touch-icon"')
        ->toContain('href="'.asset('storage/blogr/favicon/favicon.png').'"');
});

test('feature_favicon_does_not_affect_wcag_bypass_block', function () {
    view()->share('currentLocale', 'en');

    $html = View::make('blogr::layouts.blog')->render();

    expect($html)
        ->toContain('href="#main-content"')
        ->toContain('sr-only')
        ->toContain('focus:not-sr-only');
});
