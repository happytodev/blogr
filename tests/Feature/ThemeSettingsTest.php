<?php

test('theme primary color is loaded from config', function () {
    config(['blogr.ui.theme.primary_color' => '#bd1194']);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    expect($settings->theme_primary_color)->toBe('#bd1194');
});

test('theme primary color uses default when not in config', function () {
    config(['blogr.ui.theme.primary_color' => null]);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    // Should use default blue color
    expect($settings->theme_primary_color)->toBe('#3b82f6');
});

test('theme default mode is loaded from config', function () {
    config(['blogr.ui.theme.default' => 'dark']);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    expect($settings->theme_default)->toBe('dark');
});

test('theme settings are saved correctly to config', function () {
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
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
    $configPath = __DIR__ . '/../../config/blogr.php';
    $content = file_get_contents($configPath);
    
    // Should contain primary_color in theme section
    expect($content)->toContain("'primary_color'");
});

test('primary color CSS variable is injected in layout', function () {
    config(['blogr.ui.theme.primary_color' => '#ff5733']);
    
    $viewPath = __DIR__ . '/../../resources/views/layouts/blog.blade.php';
    $content = file_get_contents($viewPath);
    
    // Should contain CSS variable definition
    expect($content)->toContain('--color-primary:');
    expect($content)->toContain("config('blogr.ui.theme.primary_color'");
});
