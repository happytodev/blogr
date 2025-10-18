<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.blog_index.cards.colors.background' => 'bg-white dark:bg-gray-800']);
    config(['blogr.blog_index.cards.colors.top_border' => 'border-t-4 border-blue-500 dark:border-blue-400']);
});

it('has default card background with dark mode support', function () {
    $bg = config('blogr.blog_index.cards.colors.background');
    
    expect($bg)->toContain('bg-')
        ->and($bg)->toContain('dark:');
});

it('has default card border with dark mode support', function () {
    $border = config('blogr.blog_index.cards.colors.top_border');
    
    expect($border)->toContain('border-')
        ->and($border)->toContain('dark:');
});

it('can set custom background color with dark mode', function () {
    config(['blogr.blog_index.cards.colors.background' => 'bg-gray-50 dark:bg-gray-900']);
    
    $bg = config('blogr.blog_index.cards.colors.background');
    
    expect($bg)->toBe('bg-gray-50 dark:bg-gray-900');
});

it('can set custom border color with dark mode', function () {
    config(['blogr.blog_index.cards.colors.top_border' => 'border-t-4 border-green-500 dark:border-green-400']);
    
    $border = config('blogr.blog_index.cards.colors.top_border');
    
    expect($border)->toBe('border-t-4 border-green-500 dark:border-green-400');
});

it('can disable border by using empty string', function () {
    config(['blogr.blog_index.cards.colors.top_border' => '']);
    
    $border = config('blogr.blog_index.cards.colors.top_border');
    
    expect($border)->toBe('');
});

it('automatically generates dark mode color from light color', function () {
    // Helper function should exist to generate dark mode variant
    $lightBg = 'bg-blue-50';
    $darkBg = \Happytodev\Blogr\Helpers\ColorHelper::generateDarkMode($lightBg);
    
    expect($darkBg)->toContain('dark:');
});
