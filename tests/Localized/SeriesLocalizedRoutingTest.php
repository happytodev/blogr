<?php

use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// LocalizedTestCase is automatically used for tests in this folder (see tests/Pest.php)

it('can find series by translated slug in controller', function () {
    $user = User::factory()->create();
    
    $series = BlogSeries::factory()->create([
        'published_at' => now()->subDay(),
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'English Title',
        'slug' => 'english-slug',
        'description' => 'English description',
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Titre Français',
        'slug' => 'slug-francais',
        'description' => 'Description française',
    ]);
    
    $series->refresh();
    
    // Verify translations were created
    expect($series->translations)->toHaveCount(2);
    $enTrans = $series->translations->where('locale', 'en')->first();
    $frTrans = $series->translations->where('locale', 'fr')->first();
    expect($enTrans)->not->toBeNull();
    expect($frTrans)->not->toBeNull();
    expect($enTrans->slug)->toBe('english-slug');
    expect($frTrans->slug)->toBe('slug-francais');
    
    // Test English route
    $response = $this->get(route('blog.series', ['locale' => 'en', 'seriesSlug' => 'english-slug']));
    $response->assertStatus(200);
    
    // Test French route  
    $response = $this->get(route('blog.series', ['locale' => 'fr', 'seriesSlug' => 'slug-francais']));
    $response->assertStatus(200);
});

it('falls back to main series photo when translation has no photo', function () {
    Storage::fake('public');
    
    $mainPhoto = UploadedFile::fake()->image('main-series.jpg', 1200, 675);
    $mainPhotoPath = $mainPhoto->store('series-images', 'public');
    
    $series = BlogSeries::factory()->create([
        'slug' => 'test-series',
        'photo' => $mainPhotoPath,
        'published_at' => now()->subDay(),
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Série Test FR',
        'slug' => 'serie-test-fr',
        'description' => 'Description française',
        'photo' => null,
    ]);
    
    $response = $this->get(route('blog.series', ['locale' => 'fr', 'seriesSlug' => 'serie-test-fr']));
    
    $response->assertStatus(200);
    // The view should use the main photo since translation has no photo
    $response->assertSee('series-images/', false);
});

it('uses translation-specific photo when available', function () {
    Storage::fake('public');
    
    $mainPhoto = UploadedFile::fake()->image('main-series.jpg', 1200, 675);
    $mainPhotoPath = $mainPhoto->store('series-images', 'public');
    
    $enPhoto = UploadedFile::fake()->image('en-series.jpg', 1200, 675);
    $enPhotoPath = $enPhoto->store('series-images', 'public');
    
    $series = BlogSeries::factory()->create([
        'slug' => 'test-series',
        'photo' => $mainPhotoPath,
        'published_at' => now()->subDay(),
    ]);
    
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'English Series',
        'slug' => 'english-series',
        'description' => 'English description',
        'photo' => $enPhotoPath,
    ]);
    
    $response = $this->get(route('blog.series', ['locale' => 'en', 'seriesSlug' => 'english-series']));
    
    $response->assertStatus(200);
    // Should use EN-specific photo, not the main photo
    $response->assertSee(basename($enPhotoPath), false);
});
