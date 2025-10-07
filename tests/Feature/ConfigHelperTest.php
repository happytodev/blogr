<?php

use Happytodev\Blogr\Helpers\ConfigHelper;

test('config helper returns simple string values', function () {
    config(['test.value' => 'simple']);
    
    expect(ConfigHelper::getLocalized('test.value'))->toBe('simple');
});

test('config helper returns localized value for current locale', function () {
    app()->setLocale('fr');
    
    config(['test.localized' => [
        'en' => 'English',
        'fr' => 'Français',
    ]]);
    
    expect(ConfigHelper::getLocalized('test.localized'))->toBe('Français');
});

test('config helper falls back to default locale', function () {
    app()->setLocale('de');
    
    config([
        'blogr.locales.default' => 'en',
        'test.localized' => [
            'en' => 'English',
            'fr' => 'Français',
        ],
    ]);
    
    expect(ConfigHelper::getLocalized('test.localized'))->toBe('English');
});

test('returns non-array config values as is', function () {
    config(['test.value' => 'simple string']);
    
    $result = ConfigHelper::getLocalized('test.value');
    
    expect($result)->toBe('simple string');
});

test('returns localized value for current locale', function () {
    app()->setLocale('fr');
    
    config(['test.localized' => [
        'en' => 'English value',
        'fr' => 'Valeur française',
        'es' => 'Valor español',
    ]]);
    
    $result = ConfigHelper::getLocalized('test.localized');
    
    expect($result)->toBe('Valeur française');
});

test('falls back to default locale if current not found', function () {
    app()->setLocale('de');
    config(['blogr.locales.default' => 'en']);
    
    config(['test.localized' => [
        'en' => 'English fallback',
        'fr' => 'French value',
    ]]);
    
    $result = ConfigHelper::getLocalized('test.localized');
    
    expect($result)->toBe('English fallback');
});

test('reading time text is formatted correctly', function () {
    app()->setLocale('en');
    
    config(['blogr.reading_time.text_format' => [
        'en' => 'Reading time: {time}',
    ]]);
    
    expect(ConfigHelper::getReadingTimeText(5))->toBe('Reading time: 5 min');
});

test('reading time text works in french', function () {
    app()->setLocale('fr');
    
    config(['blogr.reading_time.text_format' => [
        'en' => 'Reading time: {time}',
        'fr' => 'Temps de lecture : {time}',
    ]]);
    
    expect(ConfigHelper::getReadingTimeText(10))->toBe('Temps de lecture : 10 min');
});

test('formats reading time text for current locale', function () {
    app()->setLocale('en');
    
    config(['blogr.reading_time.text_format' => [
        'en' => 'Reading time: {time}',
        'fr' => 'Temps de lecture : {time}',
    ]]);
    
    $result = ConfigHelper::getReadingTimeText(5);
    
    expect($result)->toBe('Reading time: 5 min');
});

test('formats reading time text for french', function () {
    app()->setLocale('fr');
    
    config(['blogr.reading_time.text_format' => [
        'en' => 'Reading time: {time}',
        'fr' => 'Temps de lecture : {time}',
    ]]);
    
    $result = ConfigHelper::getReadingTimeText(10);
    
    expect($result)->toBe('Temps de lecture : 10 min');
});

test('uses specified locale for reading time', function () {
    config(['blogr.reading_time.text_format' => [
        'en' => 'Reading time: {time}',
        'es' => 'Tiempo de lectura: {time}',
    ]]);
    
    $result = ConfigHelper::getReadingTimeText(7, 'es');
    
    expect($result)->toBe('Tiempo de lectura: 7 min');
});

test('handles missing reading time format gracefully', function () {
    app()->setLocale('zh');
    config(['blogr.reading_time.text_format' => null]);
    
    $result = ConfigHelper::getReadingTimeText(3);
    
    expect($result)->toBe('Reading time: 3 min');
});

test('handles integer minutes correctly', function () {
    app()->setLocale('en');
    
    config(['blogr.reading_time.text_format' => [
        'en' => '{time} minutes to read',
    ]]);
    
    $result = ConfigHelper::getReadingTimeText(15);
    
    expect($result)->toBe('15 min minutes to read');
});

test('reading time text accepts zero minutes', function () {
    app()->setLocale('en');
    
    config(['blogr.reading_time.text_format' => [
        'en' => 'Reading time: {time}',
    ]]);
    
    $result = ConfigHelper::getReadingTimeText(0);
    
    expect($result)->toBe('Reading time: 0 min');
});
