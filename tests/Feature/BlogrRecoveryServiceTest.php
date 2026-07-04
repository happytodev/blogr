<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Services\BlogrRecoveryService;
use Illuminate\Support\Facades\File;

it('listBackups returns empty array', function () {
    expect(app(BlogrRecoveryService::class)->listBackups())->toBeArray();
});
it('validateBackup returns valid for proper export file', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'blogr-test-').'.json';
    File::put($tmpFile, json_encode(['format_version' => '2.0', 'version' => '1.0.0', 'exported_at' => now()->toIso8601String(), 'posts' => [], 'series' => [], 'categories' => [], 'tags' => []]));
    $result = app(BlogrRecoveryService::class)->validateBackup($tmpFile);
    expect($result['valid'])->toBeTrue();
    File::delete($tmpFile);
});
it('restore performs full recovery pipeline', function () {
    $cat = Category::create(['name' => 'Test', 'slug' => 'test', 'is_default' => true]);
    $tmpFile = tempnam(sys_get_temp_dir(), 'blogr-test-').'.json';
    File::put($tmpFile, json_encode(['format_version' => '2.0', 'version' => '1.0.0', 'exported_at' => now()->toIso8601String(), 'posts' => [], 'series' => [], 'categories' => [['id' => $cat->id, 'name' => 'Updated', 'slug' => 'test', 'is_default' => true, 'created_at' => now()->toIso8601String(), 'updated_at' => now()->toIso8601String()]], 'tags' => []]));
    $result = app(BlogrRecoveryService::class)->restore($tmpFile, ['default_author_id' => 1]);
    expect($result['success'])->toBeTrue();
    File::delete($tmpFile);
});
