<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

it('has default series subtitle configured', function () {
    $subtitle = config('blogr.series.subtitle');

    expect($subtitle)->toBeArray()
        ->and($subtitle)->toHaveKey('en')
        ->and($subtitle)->toHaveKey('fr')
        ->and($subtitle)->toHaveKey('es')
        ->and($subtitle)->toHaveKey('de');
});

it('has default english subtitle', function () {
    $subtitle = config('blogr.series.subtitle.en');

    expect($subtitle)->toBe('Browse all our blog series and learn step by step.');
});

it('has default french subtitle', function () {
    $subtitle = config('blogr.series.subtitle.fr');

    expect($subtitle)->toBe('Parcourez toutes nos séries et apprenez étape par étape.');
});

it('can set custom subtitle per locale', function () {
    config(['blogr.series.subtitle.en' => 'Custom English subtitle']);

    expect(config('blogr.series.subtitle.en'))->toBe('Custom English subtitle');
});

it('can set custom subtitle for multiple locales', function () {
    config([
        'blogr.series.subtitle' => [
            'en' => 'Custom English',
            'fr' => 'Custom French',
        ],
    ]);

    expect(config('blogr.series.subtitle.en'))->toBe('Custom English')
        ->and(config('blogr.series.subtitle.fr'))->toBe('Custom French');
});

it('falls back to english subtitle when locale not found', function () {
    config(['blogr.series.subtitle' => [
        'en' => 'English subtitle',
    ]]);

    $locale = 'pl';
    $subtitle = config('blogr.series.subtitle.' . $locale)
        ?? config('blogr.series.subtitle.en')
        ?? 'Browse all our blog series and learn step by step.';

    expect($subtitle)->toBe('English subtitle');
});

it('has readable polish subtitle', function () {
    $subtitle = config('blogr.series.subtitle.pl');

    expect($subtitle)->toContain('serie');
});

it('series subtitle appears in series index seo', function () {
    $locale = 'en';
    $subtitle = config('blogr.series.subtitle.' . $locale)
        ?? config('blogr.series.subtitle.en')
        ?? 'Browse all our blog series and learn step by step.';

    expect($subtitle)->toBe(config('blogr.series.subtitle.en'));
});
