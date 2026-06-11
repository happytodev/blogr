<?php

use Happytodev\Blogr\Services\TranslationUsageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

it('can track translation usage', function () {
    $service = app(TranslationUsageService::class);

    $service->trackUsage('azure', 1500);

    $row = DB::table('blogr_translation_usage')
        ->where('provider', 'azure')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->char_count)->toBe(1500)
        ->and($row->month)->toBe(now()->month)
        ->and($row->year)->toBe((int) now()->year);
});

it('accumulates character counts for the same month', function () {
    $service = app(TranslationUsageService::class);

    $service->trackUsage('google', 1000);
    $service->trackUsage('google', 2000);

    $row = DB::table('blogr_translation_usage')
        ->where('provider', 'google')
        ->first();

    expect($row->char_count)->toBe(3000);
});

it('tracks separate providers independently', function () {
    $service = app(TranslationUsageService::class);

    $service->trackUsage('azure', 500);
    $service->trackUsage('google', 1000);

    $azure = DB::table('blogr_translation_usage')
        ->where('provider', 'azure')->first();
    $google = DB::table('blogr_translation_usage')
        ->where('provider', 'google')->first();

    expect($azure->char_count)->toBe(500)
        ->and($google->char_count)->toBe(1000);
});

it('returns usage stats with limit for Azure', function () {
    DB::table('blogr_translation_usage')->insert([
        'provider' => 'azure',
        'char_count' => 150,
        'month' => now()->month,
        'year' => now()->year,
    ]);

    Cache::forget('blogr_translation_usage_azure_' . now()->year . '_' . now()->month);

    $stats = app(TranslationUsageService::class)->getUsageStats('azure');

    expect($stats)->toMatchArray([
        'provider' => 'azure',
        'used' => 150,
        'limit' => 2_000_000,
        'remaining' => 1_999_850,
        'percentage' => 0.008,
    ]);
});

it('returns usage stats without limit for LibreTranslate', function () {
    DB::table('blogr_translation_usage')->insert([
        'provider' => 'libretranslate',
        'char_count' => 5000,
        'month' => now()->month,
        'year' => now()->year,
    ]);

    Cache::forget('blogr_translation_usage_libretranslate_' . now()->year . '_' . now()->month);

    $stats = app(TranslationUsageService::class)->getUsageStats('libretranslate');

    expect($stats)
        ->provider->toBe('libretranslate')
        ->used->toBe(5000)
        ->limit->toBeNull()
        ->remaining->toBeNull()
        ->percentage->toBeNull();
});

it('returns null when provider is none', function () {
    $stats = app(TranslationUsageService::class)->getUsageStats('none');
    expect($stats)->toBeNull();
});

it('returns null when provider is null', function () {
    $stats = app(TranslationUsageService::class)->getUsageStats(null);
    expect($stats)->toBeNull();
});

it('returns zero usage when no data exists', function () {
    Cache::forget('blogr_translation_usage_openai_' . now()->year . '_' . now()->month);

    $stats = app(TranslationUsageService::class)->getUsageStats('openai');

    expect($stats)
        ->provider->toBe('openai')
        ->used->toBe(0)
        ->limit->toBeNull();
});
