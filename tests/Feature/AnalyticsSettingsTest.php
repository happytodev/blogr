<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

// ============================================
// SETTINGS FORM TESTS
// ============================================

it('has analytics properties defined in BlogrSettings', function () {
    $settings = new BlogrSettings();

    expect(property_exists($settings, 'analytics_enabled'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_provider'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_google_measurement_id'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_plausible_domain'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_plausible_src'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_umami_website_id'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_umami_src'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_matomo_url'))->toBeTrue()
        ->and(property_exists($settings, 'analytics_matomo_site_id'))->toBeTrue();
});

it('loads google analytics settings from config', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'google',
        'blogr.analytics.google.measurement_id' => 'G-TEST123',
    ]);

    $settings = new BlogrSettings();
    $settings->mount();

    expect($settings->analytics_enabled)->toBeTrue()
        ->and($settings->analytics_provider)->toBe('google')
        ->and($settings->analytics_google_measurement_id)->toBe('G-TEST123');
});

it('loads plausible analytics settings from config', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'plausible',
        'blogr.analytics.plausible.domain' => 'mysite.com',
        'blogr.analytics.plausible.src' => 'https://plausible.mysite.com/js/script.js',
    ]);

    $settings = new BlogrSettings();
    $settings->mount();

    expect($settings->analytics_enabled)->toBeTrue()
        ->and($settings->analytics_provider)->toBe('plausible')
        ->and($settings->analytics_plausible_domain)->toBe('mysite.com')
        ->and($settings->analytics_plausible_src)->toBe('https://plausible.mysite.com/js/script.js');
});

it('loads umami analytics settings from config', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'umami',
        'blogr.analytics.umami.website_id' => 'a93a8ed3-88da-4f54-b9ce-378d8f33f06a',
        'blogr.analytics.umami.src' => 'https://cloud.umami.is/script.js',
    ]);

    $settings = new BlogrSettings();
    $settings->mount();

    expect($settings->analytics_enabled)->toBeTrue()
        ->and($settings->analytics_provider)->toBe('umami')
        ->and($settings->analytics_umami_website_id)->toBe('a93a8ed3-88da-4f54-b9ce-378d8f33f06a')
        ->and($settings->analytics_umami_src)->toBe('https://cloud.umami.is/script.js');
});

it('loads matomo analytics settings from config', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'matomo',
        'blogr.analytics.matomo.url' => 'https://matomo.mysite.com',
        'blogr.analytics.matomo.site_id' => '42',
    ]);

    $settings = new BlogrSettings();
    $settings->mount();

    expect($settings->analytics_enabled)->toBeTrue()
        ->and($settings->analytics_provider)->toBe('matomo')
        ->and($settings->analytics_matomo_url)->toBe('https://matomo.mysite.com')
        ->and($settings->analytics_matomo_site_id)->toBe('42');
});

it('defaults analytics to disabled when not configured', function () {
    config(['blogr.analytics.enabled' => null]);

    $settings = new BlogrSettings();
    $settings->mount();

    expect($settings->analytics_enabled)->toBeFalse();
});

// ============================================
// BLADE COMPONENT TESTS
// ============================================

it('renders nothing when analytics is disabled', function () {
    config(['blogr.analytics.enabled' => false]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect(trim($view))->toBe('');
});

it('renders nothing when analytics is enabled but no provider is set', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => null,
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect(trim($view))->toBe('');
});

it('renders google analytics script correctly', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'google',
        'blogr.analytics.google.measurement_id' => 'G-ABCD123456',
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->toContain('googletagmanager.com/gtag/js?id=G-ABCD123456')
        ->and($view)->toContain("gtag('config', 'G-ABCD123456')");
});

it('renders plausible analytics script with default src', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'plausible',
        'blogr.analytics.plausible.domain' => 'example.com',
        'blogr.analytics.plausible.src' => null,
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->toContain('data-domain="example.com"')
        ->and($view)->toContain('src="https://plausible.io/js/script.js"');
});

it('renders plausible analytics script with custom src', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'plausible',
        'blogr.analytics.plausible.domain' => 'example.com',
        'blogr.analytics.plausible.src' => 'https://plausible.mysite.com/js/script.js',
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->toContain('data-domain="example.com"')
        ->and($view)->toContain('src="https://plausible.mysite.com/js/script.js"');
});

it('renders umami analytics script correctly', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'umami',
        'blogr.analytics.umami.website_id' => 'a93a8ed3-88da-4f54-b9ce-378d8f33f06a',
        'blogr.analytics.umami.src' => 'https://cloud.umami.is/script.js',
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->toContain('data-website-id="a93a8ed3-88da-4f54-b9ce-378d8f33f06a"')
        ->and($view)->toContain('src="https://cloud.umami.is/script.js"');
});

it('renders matomo analytics script correctly', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'matomo',
        'blogr.analytics.matomo.url' => 'https://matomo.example.com',
        'blogr.analytics.matomo.site_id' => '5',
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->toContain('var u="https://matomo.example.com/"')
        ->and($view)->toContain("_paq.push(['setSiteId', '5'])")
        ->and($view)->toContain('matomo.php?idsite=5');
});

it('does not render google analytics without measurement id', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'google',
        'blogr.analytics.google.measurement_id' => null,
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->not->toContain('googletagmanager.com');
});

it('does not render plausible without domain', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'plausible',
        'blogr.analytics.plausible.domain' => null,
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->not->toContain('data-domain');
});

it('does not render umami without website id', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'umami',
        'blogr.analytics.umami.website_id' => null,
        'blogr.analytics.umami.src' => 'https://cloud.umami.is/script.js',
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->not->toContain('data-website-id');
});

it('does not render matomo without url', function () {
    config([
        'blogr.analytics.enabled' => true,
        'blogr.analytics.provider' => 'matomo',
        'blogr.analytics.matomo.url' => null,
        'blogr.analytics.matomo.site_id' => '5',
    ]);

    $view = view('blogr::components.analytics-tracker')->render();

    expect($view)->not->toContain('_paq');
});

// ============================================
// CONFIG TESTS
// ============================================

it('has analytics configuration in blogr config file', function () {
    $config = config('blogr.analytics');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('enabled')
        ->and($config)->toHaveKey('provider')
        ->and($config)->toHaveKey('google')
        ->and($config)->toHaveKey('plausible')
        ->and($config)->toHaveKey('umami')
        ->and($config)->toHaveKey('matomo');
});

it('has correct google analytics config structure', function () {
    $config = config('blogr.analytics.google');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('measurement_id');
});

it('has correct plausible config structure', function () {
    $config = config('blogr.analytics.plausible');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('domain')
        ->and($config)->toHaveKey('src');
});

it('has correct umami config structure', function () {
    $config = config('blogr.analytics.umami');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('website_id')
        ->and($config)->toHaveKey('src');
});

it('has correct matomo config structure', function () {
    $config = config('blogr.analytics.matomo');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('url')
        ->and($config)->toHaveKey('site_id');
});
