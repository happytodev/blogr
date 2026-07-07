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

test('feature_theme_switcher_dropdown_has_trigger_button', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toContain('x-data="themeSwitch()"')
        ->toContain('aria-expanded');
});

test('feature_theme_switcher_dropdown_has_menu_role', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toContain('role="menu"');
});

test('feature_theme_switcher_dropdown_has_three_options_with_menuitemradio_role', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toContain('role="menuitemradio"')
        ->toContain('aria-checked');
});

test('feature_theme_switcher_dropdown_has_light_option', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/role="menuitemradio".*Light/s');
});

test('feature_theme_switcher_dropdown_has_auto_option', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/role="menuitemradio".*System/s');
});

test('feature_theme_switcher_dropdown_has_dark_option', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/role="menuitemradio".*Dark/s');
});

test('feature_theme_switcher_dropdown_has_monitor_icon_for_auto', function () {
    $html = View::make('blogr::components.navigation', [
        'currentLocale' => 'en',
        'availableLocales' => ['en'],
    ])->render();

    expect($html)
        ->toMatch('/M3\.75 3A1\.75 1\.75 0 0 0 2 4\.75v8\.5/');
});
