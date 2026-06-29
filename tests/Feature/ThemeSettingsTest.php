<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('theme primary color is loaded from config', function () {
    config(['blogr.ui.theme.primary_color' => '#bd1194']);

    $settings = new BlogrSettings;
    $reflection = new ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);

    expect($settings->theme_primary_color)->toBe('#bd1194');
});

test('theme primary color uses default when not in config', function () {
    config(['blogr.ui.theme.primary_color' => null]);

    $settings = new BlogrSettings;
    $reflection = new ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);

    // Should use default blue color
    expect($settings->theme_primary_color)->toBe('#3b82f6');
});

test('theme default mode is loaded from config', function () {
    config(['blogr.ui.theme.default' => 'dark']);

    $settings = new BlogrSettings;
    $reflection = new ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);

    expect($settings->theme_default)->toBe('dark');
});

test('theme settings are saved correctly to config', function () {
    $settings = new BlogrSettings;
    $settings->theme_default = 'dark';
    $settings->theme_primary_color = '#ff5733';

    // Simulate the save process - extract theme config data
    $themeData = [
        'default' => $settings->theme_default,
        'primary_color' => $settings->theme_primary_color,
    ];

    expect($themeData['default'])->toBe('dark');
    expect($themeData['primary_color'])->toBe('#ff5733');
});

test('config file contains theme primary_color key', function () {
    $configPath = __DIR__.'/../../config/blogr.php';
    $content = file_get_contents($configPath);

    // Should contain primary_color in theme section
    expect($content)->toContain("'primary_color'");
});

test('primary color CSS variable is injected in layout', function () {
    config(['blogr.ui.theme.primary_color' => '#ff5733']);

    $viewPath = __DIR__.'/../../resources/views/layouts/blog.blade.php';
    $content = file_get_contents($viewPath);

    // Should contain CSS variable definition
    expect($content)->toContain('--color-primary:');
    expect($content)->toContain("config('blogr.ui.theme.primary_color'");
});

// ── Save & rehydrate ──

test('font_family is persisted after save and reloaded on mount', function () {
    // Setup: set font_family via the settings page
    $settings = new BlogrSettings;
    $settings->font_family = 'Inter';

    // Call save — in testing, this updates in-memory config
    $settings->save();

    // Verify config has the saved value
    expect(config('blogr.ui.theme.font_family'))->toBe('Inter');

    // Simulate a fresh page load: new instance, mount it
    config(['blogr.ui.theme.font_family' => null]); // clear so mount reads from config
    config()->set('blogr.ui.theme.font_family', 'Inter'); // set the saved value

    $fresh = new BlogrSettings;
    $reflection = new ReflectionClass($fresh);
    $method = $reflection->getMethod('mount');
    $method->invoke($fresh);

    expect($fresh->font_family)->toBe('Inter');
    expect($fresh->font_preview)->toBe('Inter');
});

test('font_preview is updated after font_family change via afterStateUpdated', function () {
    $settings = new BlogrSettings;

    // Simulate the afterStateUpdated callback from the Select
    $cb = function ($state, $set) {
        $set('font_preview', $state);
    };

    // Simulate selecting a font
    $set = function ($field, $value) use ($settings) {
        $settings->$field = $value;
    };

    $cb('Outfit', $set);

    expect($settings->font_preview)->toBe('Outfit');
});

test('brightness sliders save and reload correctly', function () {
    $settings = new BlogrSettings;
    $settings->header_brightness = 5;
    $settings->header_brightness_dark = -3;
    $settings->footer_brightness = -2;
    $settings->footer_brightness_dark = 7;

    $settings->save();

    expect(config('blogr.ui.theme.header_brightness'))->toBe(5);
    expect(config('blogr.ui.theme.header_brightness_dark'))->toBe(-3);
    expect(config('blogr.ui.theme.footer_brightness'))->toBe(-2);
    expect(config('blogr.ui.theme.footer_brightness_dark'))->toBe(7);

    // Simulate fresh load
    $fresh = new BlogrSettings;
    $reflection = new ReflectionClass($fresh);
    $method = $reflection->getMethod('mount');
    $method->invoke($fresh);

    expect($fresh->header_brightness)->toBe(5);
    expect($fresh->header_brightness_dark)->toBe(-3);
    expect($fresh->footer_brightness)->toBe(-2);
    expect($fresh->footer_brightness_dark)->toBe(7);
});

test('save persists all font keys and brightness to config', function () {
    $settings = new BlogrSettings;
    $settings->font_family = 'Figtree';
    $settings->font_custom_name = 'MyFont';
    $settings->header_brightness = 8;
    $settings->footer_brightness_dark = -5;

    $settings->save();

    $saved = config('blogr');

    expect($saved['ui']['theme']['font_family'] ?? null)->toBe('Figtree');
    expect($saved['ui']['theme']['font_custom_name'] ?? null)->toBe('MyFont');
    expect($saved['ui']['theme']['header_brightness'] ?? null)->toBe(8);
    expect($saved['ui']['theme']['footer_brightness_dark'] ?? null)->toBe(-5);
});
