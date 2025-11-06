<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Str;

it('auto-generates slug from title when creating translation without slug', function () {
    $user = User::factory()->create();
    
    $series = BlogSeries::factory()->create([
        'published_at' => now(),
    ]);
    
    // Create English translation without slug
    $enTranslation = BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'What\'s Up Devs Interviews',
        // No slug provided - should auto-generate
    ]);
    
    $series->refresh();
    
    // Should auto-generate slug from title
    expect($enTranslation->slug)->not->toBeNull();
    expect($enTranslation->slug)->toBe(Str::slug('What\'s Up Devs Interviews'));
    expect($enTranslation->slug)->toBe('whats-up-devs-interviews');
});

it('uses provided slug when explicitly set in translation', function () {
    $user = User::factory()->create();
    
    $series = BlogSeries::factory()->create([
        'published_at' => now(),
    ]);
    
    // Create translation with explicit slug
    $enTranslation = BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'Les Interviews de la Newsletter',
        'slug' => 'custom-english-slug',
    ]);
    
    expect($enTranslation->slug)->toBe('custom-english-slug');
});

it('returns correct translated slug for series', function () {
    $user = User::factory()->create();
    
    $series = BlogSeries::factory()->create([
        'published_at' => now(),
    ]);
    
    // Create English translation
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'Developer Interviews',
        'slug' => 'developer-interviews',
    ]);
    
    // Create French translation
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Interviews de Développeurs',
        'slug' => 'interviews-de-developpeurs',
    ]);
    
    $series->refresh();
    
    expect($series->getTranslatedSlug('en'))->toBe('developer-interviews');
    expect($series->getTranslatedSlug('fr'))->toBe('interviews-de-developpeurs');
});
