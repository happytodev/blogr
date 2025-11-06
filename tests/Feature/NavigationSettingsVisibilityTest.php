<?php

uses(Happytodev\Blogr\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.ui.navigation.enabled' => true]);
    config(['blogr.ui.navigation.sticky' => true]);
    config(['blogr.ui.navigation.show_logo' => true]);
    config(['blogr.ui.navigation.show_language_switcher' => true]);
    config(['blogr.ui.navigation.show_theme_switcher' => true]);
});

it('shows all navigation options when navigation is enabled', function () {
    config(['blogr.ui.navigation.enabled' => true]);
    
    expect(config('blogr.ui.navigation.enabled'))->toBeTrue()
        ->and(config('blogr.ui.navigation.sticky'))->toBeTrue()
        ->and(config('blogr.ui.navigation.show_logo'))->toBeTrue()
        ->and(config('blogr.ui.navigation.show_language_switcher'))->toBeTrue()
        ->and(config('blogr.ui.navigation.show_theme_switcher'))->toBeTrue();
});

it('can disable navigation entirely', function () {
    config(['blogr.ui.navigation.enabled' => false]);
    
    expect(config('blogr.ui.navigation.enabled'))->toBeFalse();
});

it('maintains dependent option values when navigation disabled', function () {
    config(['blogr.ui.navigation.enabled' => false]);
    config(['blogr.ui.navigation.sticky' => true]);
    config(['blogr.ui.navigation.show_logo' => true]);
    
    // Values are still stored but navigation wrapper checks enabled first
    expect(config('blogr.ui.navigation.sticky'))->toBeTrue()
        ->and(config('blogr.ui.navigation.show_logo'))->toBeTrue();
});
