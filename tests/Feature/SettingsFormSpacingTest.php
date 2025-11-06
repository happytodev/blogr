<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



test('settings form has proper spacing between form and submit button', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (!file_exists($viewPath)) {
        $viewPath = __DIR__ . '/../../resources/views/filament/pages/blogr-settings.blade.php';
    }
    
    $content = file_get_contents($viewPath);
    
    // Check that the submit button container has proper spacing classes
    expect($content)->toContain('!pt-16');
    expect($content)->toContain('!mt-16');
});

test('settings form has visible border separator', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (!file_exists($viewPath)) {
        $viewPath = __DIR__ . '/../../resources/views/filament/pages/blogr-settings.blade.php';
    }
    
    $content = file_get_contents($viewPath);
    
    // Check that the border is visible with proper styling
    expect($content)->toContain('border-t-2');
    expect($content)->toContain('border-gray-200');
    expect($content)->toContain('dark:border-gray-700');
});

test('settings form submit button is right-aligned', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (!file_exists($viewPath)) {
        $viewPath = __DIR__ . '/../../resources/views/filament/pages/blogr-settings.blade.php';
    }
    
    $content = file_get_contents($viewPath);
    
    // Check that the button container uses flex justify-end
    expect($content)->toContain('flex justify-end');
});

