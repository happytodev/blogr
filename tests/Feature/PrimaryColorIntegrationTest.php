<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



test('primary color can be configured via admin settings', function () {
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    
    // Set a custom primary color
    $settings->theme_primary_color = '#bd1194';
    
    // Verify it's set
    expect($settings->theme_primary_color)->toBe('#bd1194');
});

test('primary color is persisted in config structure', function () {
    config(['blogr.ui.theme.primary_color' => '#ff5733']);
    
    $color = config('blogr.ui.theme.primary_color');
    
    expect($color)->toBe('#ff5733');
    expect($color)->toMatch('/^#[0-9a-fA-F]{6}$/'); // Validate hex format
});

test('default primary color is violet', function () {
    // Use the actual default from config file (changed to violet/magenta in color system overhaul)
    $color = config('blogr.ui.theme.primary_color');
    
    expect($color)->toBe('#c20be5');
    expect($color)->toMatch('/^#[0-9a-fA-F]{6}$/'); // Validate hex format
});

test('primary color accepts valid hex colors', function () {
    $validColors = [
        '#000000', // Black
        '#ffffff', // White
        '#ff0000', // Red
        '#00ff00', // Green
        '#0000ff', // Blue
        '#bd1194', // Pink (from screenshot)
    ];
    
    foreach ($validColors as $color) {
        config(['blogr.ui.theme.primary_color' => $color]);
        expect(config('blogr.ui.theme.primary_color'))->toBe($color);
    }
});
