<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Illuminate\Support\Facades\File;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

it('can import blogr data from json file', function () {
    // Create a test export file (without posts to avoid user dependency)
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            ['name' => 'Imported Category', 'slug' => 'imported-category', 'is_default' => false],
        ],
        'tags' => [
            ['name' => 'Imported Tag', 'slug' => 'imported-tag'],
        ],
        'series' => [],
    ];
    
    $importPath = storage_path('app/test-import.json');
    File::put($importPath, json_encode($exportData, JSON_PRETTY_PRINT));
    
    $this->artisan('blogr:import', ['file' => $importPath])
        ->expectsOutput('✅ Blogr data imported successfully')
        ->assertExitCode(0);
    
    // Verify data was imported
    expect(Category::where('slug', 'imported-category')->exists())->toBeTrue();
    expect(Tag::where('slug', 'imported-tag')->exists())->toBeTrue();
    
    // Cleanup
    File::delete($importPath);
});

it('validates json file format before import', function () {
    $invalidPath = storage_path('app/invalid.json');
    File::put($invalidPath, 'invalid json');
    
    $this->artisan('blogr:import', ['file' => $invalidPath])
        ->assertExitCode(1);
    
    File::delete($invalidPath);
});

it('handles missing file gracefully', function () {
    $this->artisan('blogr:import', ['file' => 'nonexistent.json'])
        ->expectsOutput('❌ File not found: nonexistent.json')
        ->assertExitCode(1);
});

it('can skip existing records during import', function () {
    // Create existing category
    Category::create(['name' => 'Existing', 'slug' => 'existing', 'is_default' => false]);
    
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            ['name' => 'Existing', 'slug' => 'existing', 'is_default' => false],
            ['name' => 'New Category', 'slug' => 'new-category', 'is_default' => false],
        ],
        'tags' => [],
        'series' => [],
    ];
    
    $importPath = storage_path('app/test-skip.json');
    File::put($importPath, json_encode($exportData));
    
    $this->artisan('blogr:import', ['file' => $importPath, '--skip-existing' => true])
        ->assertExitCode(0);
    
    expect(Category::count())->toBe(3); // General (default) + Existing (skipped) + New Category
    
    File::delete($importPath);
});
