<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('displays translation-specific photo for series when available', function () {
    $mainPhoto = UploadedFile::fake()->image('main-series.jpg', 1200, 675);
    $mainPhotoPath = $mainPhoto->store('series-images', 'public');
    
    $series = BlogSeries::factory()->create([
        'slug' => 'test-series',
        'photo' => $mainPhotoPath,
        'published_at' => now(),
    ]);
    
    $enPhoto = UploadedFile::fake()->image('en-series.jpg', 1200, 675);
    $enPhotoPath = $enPhoto->store('series-images', 'public');
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'Test Series EN',
        'slug' => 'test-series-en',
        'description' => 'English description',
        'photo' => $enPhotoPath,
    ]);
    
    $response = $this->get(route('blog.series', ['locale' => 'en', 'seriesSlug' => 'test-series-en']));
    
    $response->assertStatus(200);
    $response->assertSee($enPhotoPath);
    $response->assertDontSee($mainPhotoPath);
});

it('loads translations efficiently using eager loading', function () {
    $series = BlogSeries::factory()->create([
        'slug' => 'test-series',
        'published_at' => now(),
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'Test Series EN',
        'slug' => 'test-series-en',
        'description' => 'English description',
    ]);
    
    $loadedSeries = BlogSeries::with('translations')->find($series->id);
    
    expect($loadedSeries->relationLoaded('translations'))->toBeTrue();
    
    $translation = $loadedSeries->translate('en');
    
    expect($translation)->not->toBeNull();
    expect($translation->locale)->toBe('en');
    expect($translation->title)->toBe('Test Series EN');
});
