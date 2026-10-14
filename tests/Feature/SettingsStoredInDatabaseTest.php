<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Services\SettingsRepository;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\DB;

uses(TestCase::class);

beforeEach(function () {
    app(SettingsRepository::class)->flush();
});

it('stores an admin save in the database instead of the config file', function () {
    $settings = new BlogrSettings;
    $settings->mount();
    $settings->font_family = 'Inter';
    $settings->header_brightness = 5;

    $settings->save();

    // One row per setting, keyed by its dotted config path.
    expect(DB::table(SettingsRepository::TABLE)->where('key', 'ui.theme.font_family')->value('value'))
        ->toBe('"Inter"')
        ->and(DB::table(SettingsRepository::TABLE)->where('key', 'ui.theme.header_brightness')->value('value'))
        ->toBe('5');

    // And the values apply right away, so the page reflects the save.
    expect(config('blogr.ui.theme.font_family'))->toBe('Inter')
        ->and(config('blogr.ui.theme.header_brightness'))->toBe(5);
});

it('never stores a null value, so a partially filled form cannot blank the site', function () {
    // A page saved without being mounted holds nulls for everything the form
    // would have filled.
    (new BlogrSettings)->save();

    $rows = DB::table(SettingsRepository::TABLE)->pluck('value', 'key');

    expect($rows)->not->toBeEmpty()
        ->and($rows)->not->toContain('null')
        // `analytics.provider` is one of those nulls: no row for it, so the
        // value from config/blogr.php keeps applying.
        ->and($rows->has('analytics.provider'))->toBeFalse();
});

it('applies stored values on top of the file, and leaves the other keys alone', function () {
    config(['blogr.ui.theme.primary_color' => '#111111']);
    config(['blogr.ui.theme.bg_color' => '#faf9f7']);

    DB::table(SettingsRepository::TABLE)->insert([
        'key' => 'ui.theme.primary_color',
        'value' => '"#ea580c"',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $repository = app(SettingsRepository::class);
    $repository->flush();
    $repository->applyToConfig();

    expect(config('blogr.ui.theme.primary_color'))->toBe('#ea580c')  // stored wins
        ->and(config('blogr.ui.theme.bg_color'))->toBe('#faf9f7');   // file untouched
});

it('keeps booleans, integers, empty strings and arrays intact, and drops nulls', function () {
    $repository = app(SettingsRepository::class);

    $repository->put([
        'analytics.enabled' => true,
        'analytics.anonymize_ip' => false,
        'posts_per_page' => 12,
        'cms.prefix' => '',
        'seo.site_name' => ['en' => 'Phage', 'fr' => 'Phage'],
        // null means "unset": no row, the file keeps applying for that key.
        'route.prefix' => null,
    ]);

    $repository->flush();
    $stored = app(SettingsRepository::class)->all();

    expect($stored['analytics.enabled'])->toBeTrue()
        ->and($stored['analytics.anonymize_ip'])->toBeFalse()
        ->and($stored['posts_per_page'])->toBe(12)
        ->and($stored['cms.prefix'])->toBe('')
        ->and($stored['seo.site_name'])->toBe(['en' => 'Phage', 'fr' => 'Phage'])
        ->and($stored)->not->toHaveKey('route.prefix');
});

it('resets every override, so the file values apply again', function () {
    $repository = app(SettingsRepository::class);
    $repository->put(['ui.theme.font_family' => 'Inter']);
    $repository->put(['ui.theme.primary_color' => '#ea580c']);

    expect($repository->count())->toBe(2);

    $removed = $repository->forgetAll();

    expect($removed)->toBe(2)
        ->and(DB::table(SettingsRepository::TABLE)->count())->toBe(0)
        ->and(app(SettingsRepository::class)->all())->toBe([]);
});

it('does not bake the overrides into the compiled configuration', function () {
    DB::table(SettingsRepository::TABLE)->insert([
        'key' => 'auto_save_interval',
        'value' => '45',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    config(['blogr.auto_save_interval' => 30]);

    // `php artisan config:cache` boots the application before dumping the
    // configuration: applying the overrides there would freeze them into
    // bootstrap/cache/config.php, and a reset would then have no effect.
    $_SERVER['argv'] = ['artisan', 'config:cache'];

    try {
        app(SettingsRepository::class)->applyToConfig();
    } finally {
        unset($_SERVER['argv']);
    }

    expect(config('blogr.auto_save_interval'))->toBe(30);
});

it('applies the overrides when a real request boots the application', function () {
    DB::table(SettingsRepository::TABLE)->insert([
        'key' => 'auto_save_interval',
        'value' => '45',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    config(['blogr.auto_save_interval' => 30]);

    app(SettingsRepository::class)->applyToConfig();

    expect(config('blogr.auto_save_interval'))->toBe(45);
});

it('falls back to writing the config file while the table is not migrated', function () {
    // The table is absent: the page must keep working the way it did before.
    app()->instance(SettingsRepository::class, new class extends SettingsRepository
    {
        public function hasTable(): bool
        {
            return false;
        }
    });

    $settings = new BlogrSettings;
    $settings->mount();
    $settings->font_family = 'Outfit';

    $settings->save();

    // In the testing environment the fallback applies the values in memory
    // (updateConfigFile() returns early), which is the historical behaviour.
    expect(config('blogr.ui.theme.font_family'))->toBe('Outfit')
        ->and(DB::table(SettingsRepository::TABLE)->count())->toBe(0);
});
