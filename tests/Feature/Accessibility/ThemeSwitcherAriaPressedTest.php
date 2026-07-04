<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

beforeEach(function () {
    config(['blogr.ui.navigation.enabled' => true]);
    config(['blogr.ui.navigation.show_theme_switcher' => true]);
    config(['blogr.ui.navigation.menu_items' => []]);
    config(['blogr.locales.enabled' => false]);
    config(['blogr.rss.show_in_header' => false]);
});

test('feature_theme_switcher_buttons_have_aria_pressed', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toContain('aria-pressed');
});

test('feature_theme_switcher_light_button_has_aria_pressed', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/aria-pressed=".*light.*"/');
});

test('feature_theme_switcher_auto_button_has_aria_pressed', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/aria-pressed=".*auto.*"/');
});

test('feature_theme_switcher_dark_button_has_aria_pressed', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/aria-pressed=".*dark.*"/');
});
