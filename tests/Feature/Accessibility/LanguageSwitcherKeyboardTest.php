<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.ui.navigation.enabled' => true]);
    config(['blogr.ui.navigation.show_theme_switcher' => false]);
    config(['blogr.ui.navigation.menu_items' => []]);
    config(['blogr.rss.show_in_header' => false]);
    config(['blogr.ui.navigation.show_language_switcher' => true]);
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.locales.available' => ['en', 'fr']]);

    view()->share('seoData', [
        'title' => 'Blog',
        'description' => 'Test',
    ]);
    view()->share('currentLocale', 'en');
});

test('feature_language_switcher_handles_escape_key', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en', 'fr'],
    ])->render();

    expect($html)
        ->toContain('@keydown.escape');
});

test('feature_language_switcher_trigger_has_aria_expanded', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en', 'fr'],
    ])->render();

    expect($html)
        ->toContain('aria-expanded');
});

test('feature_language_switcher_items_have_role_menuitem', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en', 'fr'],
    ])->render();

    expect($html)
        ->toContain('role="menuitem"');
});
