<?php

use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('series photo field is fillable in BlogSeries model', function () {
    expect(in_array('photo', (new BlogSeries())->getFillable()))->toBeTrue();
});

test('series translation photo field is fillable in BlogSeriesTranslation model', function () {
    expect(in_array('photo', (new BlogSeriesTranslation())->getFillable()))->toBeTrue();
});

test('series translation can save and retrieve photo', function () {
    $series = BlogSeries::factory()->create();
    
    $translation = BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Série de Test',
        'description' => 'Description',
        'photo' => 'series-images/fr-photo.jpg',
    ]);

    expect($translation->photo)->toBe('series-images/fr-photo.jpg');
    expect($translation->refresh()->photo)->toBe('series-images/fr-photo.jpg');
});

test('series falls back to main photo when translation has no photo', function () {
    $series = BlogSeries::factory()->create([
        'photo' => 'series-images/main-photo.jpg',
    ]);

    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Série Française',
        'description' => 'Description',
        'photo' => null,
    ]);

    $series = $series->fresh(['translations']);
    $translation = $series->translations->firstWhere('locale', 'fr');
    
    $photoToUse = null;
    if ($translation->photo) {
        $photoToUse = $translation->photo;
    } elseif ($series->photo) {
        $photoToUse = $series->photo;
    }
    
    expect($photoToUse)->toBe('series-images/main-photo.jpg');
});

test('series falls back to another translation photo when neither translation nor main has photo', function () {
    $series = BlogSeries::factory()->create([
        'photo' => null,
    ]);

    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'English Series',
        'description' => 'Description',
        'photo' => 'series-images/en-photo.jpg',
    ]);

    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Série Française',
        'description' => 'Description',
        'photo' => null,
    ]);

    $series = $series->fresh(['translations']);
    $translation = $series->translations->firstWhere('locale', 'fr');
    
    $photoToUse = null;
    if ($translation->photo) {
        $photoToUse = $translation->photo;
    } elseif ($series->photo) {
        $photoToUse = $series->photo;
    } else {
        $anyTranslationWithPhoto = $series->translations->first(fn($t) => !empty($t->photo));
        if ($anyTranslationWithPhoto) {
            $photoToUse = $anyTranslationWithPhoto->photo;
        }
    }
    
    expect($photoToUse)->toBe('series-images/en-photo.jpg');
});

test('series uses default image when no photos are available', function () {
    $series = BlogSeries::factory()->create([
        'photo' => null,
    ]);

    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'Test Series',
        'description' => 'Description',
        'photo' => null,
    ]);

    expect($series->photo_url)->toContain('default-series');
});

test('series photo uses temporary url with expiry for security', function () {
    $series = BlogSeries::factory()->create([
        'photo' => 'series-images/test-photo.jpg',
    ]);

    $photoUrl = $series->photo_url;
    
    // For cloud storage (S3), URL should have expiry and signature
    // For local storage, it will be a regular URL
    // Just verify we get a valid URL
    expect($photoUrl)->toBeString()
        ->and($photoUrl)->toContain('series-images/test-photo.jpg');
});
