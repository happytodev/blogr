<?php

use Happytodev\Blogr\Models\BlogSeries;

test('series model has isPublished method that returns true for past dates', function () {
    $pastSeries = BlogSeries::factory()->create([
        'published_at' => now()->subDays(1),
    ]);
    
    expect(method_exists($pastSeries, 'isPublished'))->toBeTrue();
    expect($pastSeries->isPublished())->toBeTrue();
});

test('series model isPublished returns false for future dates', function () {
    $futureSeries = BlogSeries::factory()->create([
        'published_at' => now()->addDays(1),
    ]);
    
    expect($futureSeries->isPublished())->toBeFalse();
});

test('series model isPublished returns true when published_at is null', function () {
    $alwaysPublishedSeries = BlogSeries::factory()->create([
        'published_at' => null,
    ]);
    
    expect($alwaysPublishedSeries->isPublished())->toBeTrue();
});
