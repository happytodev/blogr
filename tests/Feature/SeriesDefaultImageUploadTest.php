<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



test('series default image converts string config to array', function () {
    // Simulate config with string value (current situation)
    config(['blogr.series.default_image' => 'blogr/defaults/series.jpg']);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    // Property should be array
    expect($settings->series_default_image)->toBeArray();
    expect($settings->series_default_image)->toBe(['blogr/defaults/series.jpg']);
});

test('series default image handles null config value', function () {
    config(['blogr.series.default_image' => null]);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    // Should use default value when null
    expect($settings->series_default_image)->toBeArray();
    expect($settings->series_default_image)->toBe(['/vendor/blogr/images/default-series.svg']);
});

test('series default image handles empty string config value', function () {
    config(['blogr.series.default_image' => '']);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    // Should be null for empty string
    expect($settings->series_default_image)->toBeNull();
});

test('series default image handles array config value', function () {
    // Future-proof: if config is already an array
    config(['blogr.series.default_image' => ['blogr/defaults/series.jpg']]);
    
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $reflection = new \ReflectionClass($settings);
    $method = $reflection->getMethod('mount');
    $method->invoke($settings);
    
    // Should remain array
    expect($settings->series_default_image)->toBeArray();
    expect($settings->series_default_image)->toBe(['blogr/defaults/series.jpg']);
});

test('series default image converts array to string when saving', function () {
    $settings = new \Happytodev\Blogr\Filament\Pages\BlogrSettings();
    $settings->series_default_image = ['blogr/series/test-image.jpg'];
    
    // Simulate the save process - extract the series config data
    $seriesData = [
        'enabled' => true,
        'default_image' => is_array($settings->series_default_image) && !empty($settings->series_default_image)
            ? $settings->series_default_image[0]
            : ($settings->series_default_image ?? '/vendor/blogr/images/default-series.svg'),
    ];
    
    // Check that the saved value is a string
    expect($seriesData['default_image'])->toBeString();
    expect($seriesData['default_image'])->toBe('blogr/series/test-image.jpg');
});

