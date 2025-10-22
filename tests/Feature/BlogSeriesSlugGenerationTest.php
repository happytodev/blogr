<?php

use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates temporary slug when creating series without slug', function () {
    // Simulate Filament form data (no slug provided by user)
    $data = [
        'position' => 0,
        'photo' => 'series-images/test.jpg',
        'is_featured' => false,
        'published_at' => now(),
    ];
    
    // This should NOT fail with NOT NULL constraint
    $series = BlogSeries::create($data);
    
    expect($series)->not->toBeNull();
    expect($series->id)->not->toBeNull();
    
    // Slug should be auto-generated (temporary or default)
    expect($series->slug)->not->toBeNull();
    expect($series->slug)->not->toBe('');
});

it('preserves manually set slug when provided', function () {
    // Create a series with explicit slug
    $series = BlogSeries::create([
        'slug' => 'custom-series-slug',
        'position' => 1,
        'is_featured' => false,
        'published_at' => now(),
    ]);
    
    expect($series->slug)->toBe('custom-series-slug');
});

it('updates slug from first translation title', function () {
    // Create series without slug
    $series = BlogSeries::create([
        'position' => 1,
        'is_featured' => false,
        'published_at' => now(),
    ]);
    
    // Create first translation
    $translation = BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'My Amazing Series',
        'description' => 'A great series',
    ]);
    
    // Refresh series
    $series->refresh();
    
    // Slug should be updated from translation title
    expect($series->slug)->toBe('my-amazing-series');
});
