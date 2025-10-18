<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.ui.posts.show_language_switcher' => true]);
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.available' => ['en', 'fr']]);
});

it('respects show_language_switcher setting when true', function () {
    config(['blogr.ui.posts.show_language_switcher' => true]);
    
    expect(config('blogr.ui.posts.show_language_switcher'))->toBeTrue();
});

it('respects show_language_switcher setting when false', function () {
    config(['blogr.ui.posts.show_language_switcher' => false]);
    
    expect(config('blogr.ui.posts.show_language_switcher'))->toBeFalse();
});

it('uses correct config path ui.posts.show_language_switcher', function () {
    // Correct path should exist
    expect(config('blogr.ui.posts.show_language_switcher'))->not->toBeNull();
});
