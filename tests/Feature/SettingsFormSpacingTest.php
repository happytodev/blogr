<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('settings form has proper spacing between form and submit button', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (! file_exists($viewPath)) {
        $viewPath = __DIR__.'/../../resources/views/filament/pages/blogr-settings.blade.php';
    }

    $content = file_get_contents($viewPath);

    expect($content)->toContain('padding-top: 64px');
    expect($content)->toContain('margin-top: 64px');
});

test('settings form has visible border separator', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (! file_exists($viewPath)) {
        $viewPath = __DIR__.'/../../resources/views/filament/pages/blogr-settings.blade.php';
    }

    $content = file_get_contents($viewPath);

    expect($content)->toContain('border-top: 2px solid #e5e7eb');
});

test('settings form submit button is right-aligned', function () {
    $viewPath = resource_path('../vendor/happytodev/blogr/resources/views/filament/pages/blogr-settings.blade.php');
    if (! file_exists($viewPath)) {
        $viewPath = __DIR__.'/../../resources/views/filament/pages/blogr-settings.blade.php';
    }

    $content = file_get_contents($viewPath);

    expect($content)->toContain('justify-content: flex-end');
});
