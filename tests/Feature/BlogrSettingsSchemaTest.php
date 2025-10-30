<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;

it('blogr settings page can be instantiated without type errors', function () {
    // Test that the page can be instantiated without TypeError
    $page = app(BlogrSettings::class);
    
    expect($page)->toBeInstanceOf(BlogrSettings::class);
    
    // Test that the form property exists
    expect($page->form)->not->toBeNull();
});

it('blogr settings page mount does not throw type errors', function () {
    $page = app(BlogrSettings::class);
    
    // This should not throw a TypeError about Section vs Tab
    try {
        $page->mount();
        $success = true;
    } catch (\TypeError $e) {
        // If we get a TypeError, our fix didn't work
        $success = false;
        throw $e;
    } catch (\Exception $e) {
        // Other exceptions are OK for this test
        $success = true;
    }
    
    expect($success)->toBe(true);
});
