<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('settings form has proper spacing between form and submit button', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (! file_exists($viewPath)) {
        $viewPath = __DIR__.'/../../resources/views/filament/pages/blogr-settings.blade.php';
    }

    $content = file_get_contents($viewPath);

    expect($content)->toContain('!pt-16');
    expect($content)->toContain('!mt-16');
});

test('settings form has visible border separator', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (! file_exists($viewPath)) {
        $viewPath = __DIR__.'/../../resources/views/filament/pages/blogr-settings.blade.php';
    }

    $content = file_get_contents($viewPath);

    expect($content)->toContain('border-t-2');
});

test('settings form submit button is right-aligned', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (! file_exists($viewPath)) {
        $viewPath = __DIR__.'/../../resources/views/filament/pages/blogr-settings.blade.php';
    }

    $content = file_get_contents($viewPath);

    expect($content)->toContain('flex justify-end');
});
