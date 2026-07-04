<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.ui.navigation.enabled' => true]);
    config(['blogr.ui.navigation.show_theme_switcher' => false]);
    config(['blogr.rss.show_in_header' => false]);
    config(['blogr.locales.enabled' => false]);
    config(['blogr.ui.navigation.menu_items' => [
        [
            'labels' => [['locale' => 'en', 'label' => 'Services']],
            'type' => 'megamenu',
            'children' => [
                ['labels' => [['locale' => 'en', 'label' => 'Web Design']], 'type' => 'external', 'url' => '#'],
                ['labels' => [['locale' => 'en', 'label' => 'SEO']], 'type' => 'external', 'url' => '#'],
            ],
        ],
    ]]);
});

test('feature_mega_menu_trigger_has_aria_expanded', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toContain('aria-expanded');
});

test('feature_mega_menu_trigger_has_aria_haspopup', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toContain('aria-haspopup="true"');
});

test('feature_mega_menu_opens_on_focus', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    // Should have @focus handling in addition to @mouseenter
    expect($html)
        ->toContain('@mouseenter')
        ->toContain('@focus');
});
