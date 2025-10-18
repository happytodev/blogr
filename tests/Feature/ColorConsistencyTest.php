<?php

test('series list component does not contain hardcoded purple colors', function () {
    $content = file_get_contents(__DIR__ . '/../../resources/views/components/series-list.blade.php');
    
    // Should NOT contain hardcoded purple colors
    expect($content)->not->toContain('text-purple-600');
    expect($content)->not->toContain('text-purple-400');
    expect($content)->not->toContain('border-purple-500');
    expect($content)->not->toContain('from-purple-50');
    expect($content)->not->toContain('hover:text-purple-600');
});

test('series authors component does not contain hardcoded blue ring colors', function () {
    $content = file_get_contents(__DIR__ . '/../../resources/views/components/series-authors.blade.php');
    
    // Should NOT contain hardcoded blue ring colors
    expect($content)->not->toContain('hover:ring-blue-500');
    expect($content)->not->toContain('hover:ring-blue-400');
    
    // Should contain CSS variable for hover ring
    expect($content)->toContain('hover:ring-[var(--color-primary)]');
});

test('author bio component does not contain hardcoded blue/purple gradient', function () {
    $content = file_get_contents(__DIR__ . '/../../resources/views/components/author-bio.blade.php');
    
    // Should NOT contain hardcoded blue/purple gradient
    expect($content)->not->toContain('from-blue-500');
    expect($content)->not->toContain('to-purple-600');
    
    // Should contain CSS variable for gradient
    expect($content)->toContain('from-[var(--color-primary)]');
    expect($content)->toContain('to-[var(--color-primary-dark)]');
});

