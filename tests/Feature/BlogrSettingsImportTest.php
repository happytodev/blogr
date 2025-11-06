<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CategoryTranslation;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Services\BlogrImportService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Create admin user with proper role
    $this->admin = User::factory()->create();
    
    // Make sure the user has admin role using Spatie Permission
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->admin->assignRole($adminRole);
    
    actingAs($this->admin);
});

it('import service handles empty file array gracefully', function () {
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile('');
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeFalse()
        ->and($result['errors'])->toContain('File does not exist');
});

it('import service validates JSON structure', function () {
    Storage::fake('local');
    
    $invalidData = ['invalid' => 'structure'];
    $filePath = storage_path('app/test-invalid.json');
    File::put($filePath, json_encode($invalidData));
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
    
    File::delete($filePath);
});

it('import service successfully imports valid JSON file', function () {
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            ['name' => 'Service Test Category', 'slug' => 'service-test-category', 'is_default' => false],
        ],
        'tags' => [
            ['name' => 'Service Test Tag', 'slug' => 'service-test-tag'],
        ],
        'series' => [],
    ];
    
    $filePath = storage_path('app/test-service-import.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue();
    
    // Verify data was imported
    expect(Category::where('slug', 'service-test-category')->exists())->toBeTrue();
    expect(Tag::where('slug', 'service-test-tag')->exists())->toBeTrue();
    
    File::delete($filePath);
});

it('import service handles corrupted ZIP file', function () {
    $zipPath = storage_path('app/corrupted.zip');
    File::put($zipPath, 'not a real zip');
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($zipPath);
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
    
    File::delete($zipPath);
});

it('import service imports ZIP file with media', function () {
    // Create a real ZIP file
    $zipPath = storage_path('app/test-with-media.zip');
    $zip = new ZipArchive();
    
    if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
        $exportData = [
            'version' => '0.12.5',
            'exported_at' => now()->toIso8601String(),
            'posts' => [],
            'categories' => [
                ['name' => 'ZIP Test', 'slug' => 'zip-test', 'is_default' => false],
            ],
            'tags' => [],
            'series' => [],
            'media_files' => [],
        ];
        
        $zip->addFromString('data.json', json_encode($exportData));
        $zip->close();
    }
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($zipPath);
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue();
    
    expect(Category::where('slug', 'zip-test')->exists())->toBeTrue();
    
    File::delete($zipPath);
});

it('only allows admin users to access settings page', function () {
    expect(BlogrSettings::canAccess())->toBeTrue();
})->skip('Requires Filament bindings - BlogrSettings::canAccess() uses Filament::auth()');

it('denies non-admin users access to settings page', function () {
    // Create a non-admin user
    $user = User::factory()->create();
    actingAs($user);
    
    expect(BlogrSettings::canAccess())->toBeFalse();
})->skip('Requires Filament bindings - BlogrSettings::canAccess() uses Filament::auth()');

it('updates existing categories instead of failing on duplicate', function () {
    // Create an existing category
    $existingCategory = Category::create([
        'name' => 'Existing Category',
        'slug' => 'existing-category',
        'is_default' => false,
    ]);
    
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            [
                'name' => 'Updated Category Name',
                'slug' => 'existing-category', // Same slug
                'is_default' => false,
            ],
        ],
        'tags' => [],
        'series' => [],
    ];
    
    $filePath = storage_path('app/test-update-category.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['results']['categories']['updated'] ?? 0)->toBe(1)
        ->and($result['results']['categories']['imported'] ?? 0)->toBe(0);
    
    // Verify the category was updated, not duplicated
    expect(Category::where('slug', 'existing-category')->count())->toBe(1);
    expect(Category::where('slug', 'existing-category')->first()->name)->toBe('Updated Category Name');
    
    File::delete($filePath);
});

it('creates new categories when they do not exist', function () {
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            ['name' => 'Brand New Category', 'slug' => 'brand-new-category', 'is_default' => false],
        ],
        'tags' => [],
        'series' => [],
    ];
    
    $filePath = storage_path('app/test-new-category.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);
    
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['results']['categories']['imported'] ?? 0)->toBe(1)
        ->and($result['results']['categories']['updated'] ?? 0)->toBe(0);
    
    expect(Category::where('slug', 'brand-new-category')->exists())->toBeTrue();
    
    File::delete($filePath);
});

it('skips existing category translations during import', function () {
    // Create existing category with translation
    $category = Category::create([
        'name' => 'Interview',
        'slug' => 'interview',
        'is_default' => false,
    ]);
    
    $translation = \Happytodev\Blogr\Models\CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'name' => 'Interview',
        'slug' => 'interview',
        'description' => 'Existing description',
    ]);

    // Prepare import data with same translation
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            [
                'id' => $category->id,
                'name' => 'Interview',
                'slug' => 'interview',
                'is_default' => false,
            ],
        ],
        'category_translations' => [
            [
                'category_id' => $category->id,
                'locale' => 'en',
                'name' => 'Interview',
                'slug' => 'interview',
                'description' => 'New description from import',
            ],
        ],
        'tags' => [],
        'series' => [],
    ];

    $filePath = storage_path('app/test-skip-category-translation.json');
    File::put($filePath, json_encode($exportData));

    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);

    expect($result['success'])->toBeTrue();
    
    // Verify category translation was not duplicated
    expect(\Happytodev\Blogr\Models\CategoryTranslation::where('locale', 'en')->where('slug', 'interview')->count())->toBe(1);
    
    // Verify original description is preserved (not updated)
    $freshTranslation = \Happytodev\Blogr\Models\CategoryTranslation::where('locale', 'en')->where('slug', 'interview')->first();
    expect($freshTranslation->description)->toBe('Existing description');
    
    File::delete($filePath);
});

it('skips existing tag translations during import', function () {
    // Create existing tag with translation
    $tag = Tag::create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    
    \Happytodev\Blogr\Models\TagTranslation::create([
        'tag_id' => $tag->id,
        'locale' => 'en',
        'name' => 'Laravel',
        'slug' => 'laravel',
        'description' => 'Existing tag description',
    ]);

    // Prepare import data with same translation
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [],
        'tags' => [
            [
                'id' => $tag->id,
                'name' => 'Laravel',
                'slug' => 'laravel',
            ],
        ],
        'tag_translations' => [
            [
                'tag_id' => $tag->id,
                'locale' => 'en',
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'New tag description from import',
            ],
        ],
        'series' => [],
    ];

    $filePath = storage_path('app/test-skip-tag-translation.json');
    File::put($filePath, json_encode($exportData));

    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);

    expect($result['success'])->toBeTrue();
    
    // Verify tag translation was not duplicated
    expect(\Happytodev\Blogr\Models\TagTranslation::where('locale', 'en')->where('slug', 'laravel')->count())->toBe(1);
    
    // Verify original description is preserved
    $freshTranslation = \Happytodev\Blogr\Models\TagTranslation::where('locale', 'en')->where('slug', 'laravel')->first();
    expect($freshTranslation->description)->toBe('Existing tag description');
    
    File::delete($filePath);
});

it('imports category translations when they do not exist', function () {
    // Create existing category WITHOUT translation
    $category = Category::create([
        'name' => 'Interview',
        'slug' => 'interview',
        'is_default' => false,
    ]);

    // Prepare import data with new translation
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            [
                'id' => $category->id,
                'name' => 'Interview',
                'slug' => 'interview',
                'is_default' => false,
            ],
        ],
        'category_translations' => [
            [
                'category_id' => $category->id,
                'locale' => 'fr',
                'name' => 'Interview',
                'slug' => 'interview',
                'description' => 'Description en français',
            ],
        ],
        'tags' => [],
        'series' => [],
    ];

    $filePath = storage_path('app/test-new-category-translation.json');
    File::put($filePath, json_encode($exportData));

    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);

    expect($result['success'])->toBeTrue();
    
    // Verify new translation was created
    expect(\Happytodev\Blogr\Models\CategoryTranslation::where('locale', 'fr')->where('slug', 'interview')->count())->toBe(1);
    
    $freshTranslation = \Happytodev\Blogr\Models\CategoryTranslation::where('locale', 'fr')->where('slug', 'interview')->first();
    expect($freshTranslation->description)->toBe('Description en français');
    
    File::delete($filePath);
});

it('has overwrite toggle in import form (disabled by default)', function () {
    $settingsPage = app(BlogrSettings::class);
    
    // Verify the page has the overwrite_existing_data property
    expect($settingsPage)->toHaveProperty('overwrite_existing_data');
    
    // Verify default value is false
    expect($settingsPage->overwrite_existing_data)->toBeFalse();
    
    // Verify the page can be instantiated without errors
    expect($settingsPage)->toBeInstanceOf(BlogrSettings::class);
});

it('deletes all blog data except users when overwrite is enabled', function () {
    // Create some existing data
    $user = User::factory()->create(['name' => 'Test User']);
    $category = Category::create(['name' => 'Old Category', 'slug' => 'old-category', 'is_default' => false]);
    $tag = Tag::create(['name' => 'Old Tag', 'slug' => 'old-tag']);
    
    // Prepare import data with overwrite option
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            ['name' => 'New Category', 'slug' => 'new-category', 'is_default' => false],
        ],
        'tags' => [
            ['name' => 'New Tag', 'slug' => 'new-tag'],
        ],
        'series' => [],
    ];

    $filePath = storage_path('app/test-overwrite.json');
    File::put($filePath, json_encode($exportData));

    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath, ['overwrite' => true]);

    expect($result['success'])->toBeTrue();
    
    // Verify old data was deleted
    expect(Category::where('slug', 'old-category')->exists())->toBeFalse();
    expect(Tag::where('slug', 'old-tag')->exists())->toBeFalse();
    
    // Verify new data was imported
    expect(Category::where('slug', 'new-category')->exists())->toBeTrue();
    expect(Tag::where('slug', 'new-tag')->exists())->toBeTrue();
    
    // Verify user was NOT deleted
    expect(User::where('name', 'Test User')->exists())->toBeTrue();
    
    File::delete($filePath);
});

it('keeps existing data when overwrite is disabled (default)', function () {
    // Create some existing data
    $category = Category::create(['name' => 'Existing Category', 'slug' => 'existing-category', 'is_default' => false]);
    
    // Prepare import data without overwrite option
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [
            ['name' => 'New Category', 'slug' => 'new-category', 'is_default' => false],
        ],
        'tags' => [],
        'series' => [],
    ];

    $filePath = storage_path('app/test-no-overwrite.json');
    File::put($filePath, json_encode($exportData));

    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath); // No overwrite option

    expect($result['success'])->toBeTrue();
    
    // Verify old data still exists
    expect(Category::where('slug', 'existing-category')->exists())->toBeTrue();
    
    // Verify new data was also imported
    expect(Category::where('slug', 'new-category')->exists())->toBeTrue();
    
    File::delete($filePath);
});

it('maps orphaned post authors to default user when specified', function () {
    // Create a default user to map orphaned posts to
    $defaultUser = User::factory()->create(['name' => 'Default Author', 'email' => 'default@example.com']);
    
    // Create a category for the posts
    $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'is_default' => false]);
    CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'name' => 'Tech',
        'slug' => 'tech',
    ]);
    
    // Prepare import data with post that has non-existent user_id (999)
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [
            [
                'id' => 1,
                'user_id' => 999, // This user doesn't exist
                'category_id' => $category->id,
                'published_at' => now()->toIso8601String(),
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'post_translations' => [
            [
                'id' => 1,
                'blog_post_id' => 1,
                'locale' => 'en',
                'title' => 'Orphaned Post',
                'slug' => 'orphaned-post',
                'content' => 'Content of orphaned post',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'post_translation_categories' => [
            [
                'blog_post_translation_id' => 1,
                'category_id' => $category->id,
            ],
        ],
        'post_translation_tags' => [],
        'categories' => [],
        'category_translations' => [],
        'tags' => [],
        'tag_translations' => [],
        'series' => [],
        'series_translations' => [],
        'user_translations' => [],
    ];
    
    $filePath = storage_path('app/test-import-orphaned-authors.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath, [
        'default_author_id' => $defaultUser->id,
    ]);
    
    expect($result['success'])->toBeTrue();
    
    // Verify post was created with default user
    $post = BlogPost::find(1);
    expect($post)->not->toBeNull();
    expect($post->user_id)->toBe($defaultUser->id);
    
    File::delete($filePath);
});

it('fails to import orphaned posts when no default author specified', function () {
    // Create a category for the posts
    $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'is_default' => false]);
    CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'name' => 'Tech',
        'slug' => 'tech',
    ]);
    
    // Prepare import data with post that has non-existent user_id (999)
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [
            [
                'id' => 1,
                'user_id' => 999, // This user doesn't exist
                'category_id' => $category->id,
                'published_at' => now()->toIso8601String(),
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'post_translations' => [
            [
                'id' => 1,
                'blog_post_id' => 1,
                'locale' => 'en',
                'title' => 'Orphaned Post',
                'slug' => 'orphaned-post',
                'content' => 'Content of orphaned post',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'post_translation_categories' => [
            [
                'blog_post_translation_id' => 1,
                'category_id' => $category->id,
            ],
        ],
        'post_translation_tags' => [],
        'categories' => [],
        'category_translations' => [],
        'tags' => [],
        'tag_translations' => [],
        'series' => [],
        'series_translations' => [],
        'user_translations' => [],
    ];
    
    $filePath = storage_path('app/test-import-orphaned-authors-no-default.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    
    // Import should fail or skip the post when no default author is provided
    $result = $service->importFromFile($filePath); // No default_author_id
    
    // The import might succeed but skip the orphaned post
    expect($result['success'])->toBeTrue();
    
    // Verify post was NOT created (because user doesn't exist)
    $post = BlogPost::where('slug', 'orphaned-post')->first();
    expect($post)->toBeNull();
    
    File::delete($filePath);
});

it('skips existing series translations during import', function () {
    // Create a series with translation
    $series = \Happytodev\Blogr\Models\BlogSeries::create([
        'slug' => 'test-series',
        'is_featured' => false,
    ]);
    
    \Happytodev\Blogr\Models\BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'fr',
        'title' => 'Série de test',
        'slug' => 'test-series',
    ]);
    
    // Prepare import data with same translation (should be skipped)
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'post_translations' => [],
        'post_translation_categories' => [],
        'post_translation_tags' => [],
        'categories' => [],
        'category_translations' => [],
        'tags' => [],
        'tag_translations' => [],
        'series' => [
            [
                'id' => $series->id,
                'slug' => 'test-series',
                'is_featured' => false,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'series_translations' => [
            [
                'blog_series_id' => $series->id,
                'locale' => 'fr',
                'title' => 'Série de test (updated)',
                'slug' => 'test-series',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'user_translations' => [],
    ];
    
    $filePath = storage_path('app/test-import-series-translations.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    
    // Pass skip_existing option to actually skip (not update)
    $result = $service->importFromFile($filePath, ['skip_existing' => true]);
    
    expect($result['success'])->toBeTrue();
    expect($result['results']['series_translations']['skipped'])->toBe(1);
    expect($result['results']['series_translations']['imported'])->toBe(0);
    
    // Verify translation was NOT updated (kept original)
    $translation = \Happytodev\Blogr\Models\BlogSeriesTranslation::where('blog_series_id', $series->id)
        ->where('locale', 'fr')
        ->first();
    expect($translation->title)->toBe('Série de test'); // Original title, not updated
    
    File::delete($filePath);
});

it('imports series translations when they do not exist', function () {
    // Create a series without translation
    $series = \Happytodev\Blogr\Models\BlogSeries::create([
        'slug' => 'new-series',
        'is_featured' => false,
    ]);
    
    // Prepare import data with new translation
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'post_translations' => [],
        'post_translation_categories' => [],
        'post_translation_tags' => [],
        'categories' => [],
        'category_translations' => [],
        'tags' => [],
        'tag_translations' => [],
        'series' => [
            [
                'id' => $series->id,
                'slug' => 'new-series',
                'is_featured' => false,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'series_translations' => [
            [
                'blog_series_id' => $series->id,
                'locale' => 'en',
                'title' => 'New Series',
                'slug' => 'new-series',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'user_translations' => [],
    ];
    
    $filePath = storage_path('app/test-import-new-series-translations.json');
    File::put($filePath, json_encode($exportData));
    
    $service = app(BlogrImportService::class);
    $result = $service->importFromFile($filePath);
    
    expect($result['success'])->toBeTrue();
    expect($result['results']['series_translations']['imported'])->toBe(1);
    
    // Verify translation was created
    $translation = \Happytodev\Blogr\Models\BlogSeriesTranslation::where('blog_series_id', $series->id)
        ->where('locale', 'en')
        ->first();
    expect($translation)->not->toBeNull();
    expect($translation->title)->toBe('New Series');
    
    File::delete($filePath);
});
