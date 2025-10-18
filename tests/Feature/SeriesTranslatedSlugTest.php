<?php

use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\User;

it('can create a series with translated slugs', function () {
    $user = User::factory()->create();
    
    $series = BlogSeries::factory()->create([
        'slug' => 'my-series',
        'published_at' => now(),
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'slug' => 'my-series',
        'title' => 'My Series',
        'description' => 'English description',
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'slug' => 'ma-serie',
        'title' => 'Ma Série',
        'description' => 'Description française',
    ]);
    
    $series->refresh();
    
    expect($series->translations)->toHaveCount(2);
    expect($series->getTranslatedSlug('en'))->toBe('my-series');
    expect($series->getTranslatedSlug('fr'))->toBe('ma-serie');
    expect($series->getTranslatedSlug('es'))->toBe('my-series');
});
