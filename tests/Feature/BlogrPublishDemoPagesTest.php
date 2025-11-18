<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Database\Seeders\CmsPageSeeder;
use Happytodev\Blogr\Services\CmsPageBackupService;

it('seeds demo cms pages successfully', function () {
    // Verify no pages exist initially
    expect(CmsPage::count())->toBe(0);

    // Run seeder
    $seeder = new CmsPageSeeder();
    $seeder->run();

    // Verify pages were created
    expect(CmsPage::count())->toBe(2);

    // Verify home page
    $homePage = CmsPage::where('slug', 'home-page')->first();
    expect($homePage)->not->toBeNull();
    expect($homePage->is_homepage)->toBeTrue();
    expect($homePage->is_published)->toBeTrue();
    expect($homePage->blocks)->toBeArray();
    expect(count($homePage->blocks))->toBeGreaterThanOrEqual(1);

    // Verify contact page
    $contactPage = CmsPage::where('slug', 'contact')->first();
    expect($contactPage)->not->toBeNull();
    expect($contactPage->is_published)->toBeTrue();
    expect($contactPage->blocks)->toBeArray();
});

it('creates multilingual translations for demo pages', function () {
    $seeder = new CmsPageSeeder();
    $seeder->run();

    // Check English and French translations exist
    $homePage = CmsPage::where('slug', 'home-page')->first();
    expect($homePage->translations()->count())->toBe(2);

    $enTrans = $homePage->translations()->where('locale', 'en')->first();
    expect($enTrans)->not->toBeNull();
    expect($enTrans->title)->toBe('Welcome to Blogr');
    expect($enTrans->slug)->toBe('home');

    $frTrans = $homePage->translations()->where('locale', 'fr')->first();
    expect($frTrans)->not->toBeNull();
    expect($frTrans->title)->toBe('Bienvenue sur Blogr');
    expect($frTrans->slug)->toBe('accueil');
});

it('demo pages have valid block structure', function () {
    $seeder = new CmsPageSeeder();
    $seeder->run();

    $homePage = CmsPage::where('slug', 'home-page')->first();
    $blocks = $homePage->blocks;

    // Verify hero block
    $heroBlock = collect($blocks)->firstWhere('type', 'hero');
    expect($heroBlock)->not->toBeNull();
    expect($heroBlock['data']['title'])->toContain('Blogr');

    // Verify features block
    $featuresBlock = collect($blocks)->firstWhere('type', 'features');
    expect($featuresBlock)->not->toBeNull();
    expect($featuresBlock['data']['items'])->toBeArray();
    expect(count($featuresBlock['data']['items']))->toBeGreaterThanOrEqual(3);

    // Verify content block
    $contentBlock = collect($blocks)->firstWhere('type', 'content');
    expect($contentBlock)->not->toBeNull();
    expect($contentBlock['data']['content'])->toContain('FilamentPHP');

    // Verify CTA block
    $ctaBlock = collect($blocks)->firstWhere('type', 'cta');
    expect($ctaBlock)->not->toBeNull();
});

it('publish demo pages command works with force option', function () {
    $this->artisan('blogr:publish-demo-pages', ['--force' => true])
        ->assertExitCode(0);

    expect(CmsPage::count())->toBe(2);
    expect(CmsPage::where('slug', 'home-page')->exists())->toBeTrue();
    expect(CmsPage::where('slug', 'contact')->exists())->toBeTrue();
});

it('prevents overwriting demo pages without force', function () {
    // Create initial demo pages
    $this->artisan('blogr:publish-demo-pages', ['--force' => true])->assertExitCode(0);

    // Try to create again without force - should ask for confirmation
    $this->artisan('blogr:publish-demo-pages')
        ->expectsQuestion('Do you want to overwrite them? Use --force to skip this prompt.', 'no')
        ->assertExitCode(0);
});

it('creates backup when backup option is used', function () {
    $backupService = new CmsPageBackupService();

    // Clean up backups first
    foreach ($backupService->listBackups() as $backup) {
        $backupService->deleteBackup($backup['path']);
    }

    // Publish demo pages with backup
    $this->artisan('blogr:publish-demo-pages', [
        '--force' => true,
        '--backup' => true,
    ])->assertExitCode(0);

    // Verify backup was created
    $backups = $backupService->listBackups();
    expect(count($backups))->toBeGreaterThanOrEqual(1);
});

it('handles demo pages correctly with updateOrCreate', function () {
    // Create first time
    $this->artisan('blogr:publish-demo-pages', ['--force' => true])->assertExitCode(0);
    $firstCount = CmsPage::count();

    // Create again with force (should not increase count)
    $this->artisan('blogr:publish-demo-pages', ['--force' => true])->assertExitCode(0);
    expect(CmsPage::count())->toBe($firstCount);
});
