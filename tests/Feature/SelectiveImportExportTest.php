<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\BlogrExportService;
use Happytodev\Blogr\Services\BlogrImportService;

beforeEach(function () {
    Category::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'is_default' => true]);
    Tag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);
});
it('export with --only exports only specified sections', function () {
    $data = app(BlogrExportService::class)->export(['only' => ['posts', 'categories']]);
    expect($data)->toHaveKey('posts')->toHaveKey('categories');
    expect($data)->not->toHaveKey('tags');
});
it('export with --skip removes specified sections', function () {
    $data = app(BlogrExportService::class)->export(['skip' => ['tags', 'series']]);
    expect($data)->toHaveKey('posts')->toHaveKey('categories');
    expect($data)->not->toHaveKey('tags');
});
it('import with --only imports only specified sections', function () {
    $user = User::factory()->create();
    $defaultCat = Category::where('is_default', true)->first();
    $result = app(BlogrImportService::class)->import([
        'version' => '1.0.0', 'exported_at' => now()->toIso8601String(),
        'posts' => [['id' => 1, 'published_at' => now()->toIso8601String(), 'user_id' => $user->id, 'category_id' => $defaultCat->id, 'created_at' => now()->toIso8601String(), 'updated_at' => now()->toIso8601String()]],
        'series' => [], 'categories' => [], 'tags' => [],
    ], ['only' => ['posts']]);
    expect($result['success'])->toBeTrue();
});
it('import with --skip ignores specified sections', function () {
    $result = app(BlogrImportService::class)->import([
        'version' => '1.0.0', 'exported_at' => now()->toIso8601String(),
        'posts' => [], 'series' => [],
        'categories' => [['id' => 98, 'name' => 'Skipped', 'slug' => 'skipped', 'is_default' => false, 'created_at' => now()->toIso8601String(), 'updated_at' => now()->toIso8601String()]],
        'tags' => [],
    ], ['skip' => ['categories']]);
    expect($result['success'])->toBeTrue();
    expect(Category::where('slug', 'skipped')->exists())->toBeFalse();
});
