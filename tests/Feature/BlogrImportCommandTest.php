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

it('can import users with their roles', function () {
    // Setup roles first
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [],
        'tags' => [],
        'series' => [],
        'users' => [
            [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin.import@test.com',
                'password' => bcrypt('password'),
                'roles' => ['admin'],
            ],
            [
                'id' => 2,
                'name' => 'Writer User',
                'email' => 'writer.import@test.com',
                'password' => bcrypt('password'),
                'roles' => ['writer'],
            ],
        ],
    ];
    
    $importPath = storage_path('app/test-users-import.json');
    File::put($importPath, json_encode($exportData, JSON_PRETTY_PRINT));
    
    $this->artisan('blogr:import', ['file' => $importPath])
        ->assertExitCode(0);
    
    // Verify users were imported with correct roles
    $adminUser = \Happytodev\Blogr\Models\User::where('email', 'admin.import@test.com')->first();
    expect($adminUser)->not->toBeNull();
    expect($adminUser->hasRole('admin'))->toBeTrue();
    
    $writerUser = \Happytodev\Blogr\Models\User::where('email', 'writer.import@test.com')->first();
    expect($writerUser)->not->toBeNull();
    expect($writerUser->hasRole('writer'))->toBeTrue();
    
    // Cleanup
    File::delete($importPath);
});

it('reassigns roles correctly on reimport', function () {
    // Setup roles
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    
    // Create initial user with writer role
    $user = \Happytodev\Blogr\Models\User::factory()->create(['email' => 'role.change@test.com']);
    $user->syncRoles('writer');
    $userId = $user->id;
    
    // Import data that changes role to admin
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [],
        'tags' => [],
        'series' => [],
        'users' => [
            [
                'id' => $userId,
                'name' => 'Updated User',
                'email' => 'role.change@test.com',
                'password' => bcrypt('password'),
                'roles' => ['admin'], // Changed from writer to admin
            ],
        ],
    ];
    
    $importPath = storage_path('app/test-role-change.json');
    File::put($importPath, json_encode($exportData, JSON_PRETTY_PRINT));
    
    // Import without skip-existing (allows overwrite)
    $this->artisan('blogr:import', ['file' => $importPath])
        ->assertExitCode(0);
    
    // Verify role was updated
    $updatedUser = \Happytodev\Blogr\Models\User::find($userId);
    expect($updatedUser->hasRole('admin'))->toBeTrue();
    expect($updatedUser->hasRole('writer'))->toBeFalse();
    
    // Cleanup
    File::delete($importPath);
});

it('handles invalid roles gracefully during import', function () {
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'categories' => [],
        'tags' => [],
        'series' => [],
        'users' => [
            [
                'id' => 999,
                'name' => 'User With Invalid Role',
                'email' => 'invalid.role@test.com',
                'password' => bcrypt('password'),
                'roles' => ['nonexistent_role'], // This role doesn't exist
            ],
        ],
    ];
    
    $importPath = storage_path('app/test-invalid-role.json');
    File::put($importPath, json_encode($exportData, JSON_PRETTY_PRINT));
    
    // Import should still succeed but log warning about invalid role
    $this->artisan('blogr:import', ['file' => $importPath])
        ->assertExitCode(0);
    
    // User should be created even if role is invalid
    $user = \Happytodev\Blogr\Models\User::where('email', 'invalid.role@test.com')->first();
    expect($user)->not->toBeNull();
    
    // Cleanup
    File::delete($importPath);
});

it('can import cms pages with their translations and blocks', function () {
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'post_translations' => [],
        'categories' => [],
        'category_translations' => [],
        'tags' => [],
        'tag_translations' => [],
        'series' => [],
        'series_translations' => [],
        'users' => [],
        'user_translations' => [],
        'post_translation_categories' => [],
        'post_translation_tags' => [],
        'cms_pages' => [
            [
                'id' => 1,
                'slug' => 'imported-page',
                'template' => 'default',
                'blocks' => [
                    ['id' => 'hero', 'type' => 'hero', 'content' => 'Welcome to imported page'],
                ],
                'is_published' => true,
                'is_homepage' => false,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'cms_page_translations' => [
            [
                'id' => 1,
                'cms_page_id' => 1,
                'locale' => 'en',
                'slug' => 'imported-page-en',
                'title' => 'Imported Page',
                'meta_description' => 'An imported CMS page',
                'content' => 'This is the content',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
            [
                'id' => 2,
                'cms_page_id' => 1,
                'locale' => 'fr',
                'slug' => 'page-importee-fr',
                'title' => 'Page Importée',
                'meta_description' => 'Une page CMS importée',
                'content' => 'Ceci est le contenu',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
    ];
    
    $importPath = storage_path('app/test-cms-import.json');
    File::put($importPath, json_encode($exportData, JSON_PRETTY_PRINT));
    
    $this->artisan('blogr:import', ['file' => $importPath])
        ->assertExitCode(0);
    
    // Verify CMS page was imported
    $page = \Happytodev\Blogr\Models\CmsPage::where('slug', 'imported-page')->first();
    expect($page)->not->toBeNull();
    expect($page->blocks)->toBeArray();
    expect(count($page->blocks))->toBe(1);
    expect($page->blocks[0]['type'])->toBe('hero');
    
    // Verify translations
    $enTranslation = $page->translations()->where('locale', 'en')->first();
    expect($enTranslation)->not->toBeNull();
    expect($enTranslation->title)->toBe('Imported Page');
    
    $frTranslation = $page->translations()->where('locale', 'fr')->first();
    expect($frTranslation)->not->toBeNull();
    expect($frTranslation->title)->toBe('Page Importée');
    
    // Cleanup
    File::delete($importPath);
});

it('preserves blocks structure during cms page import', function () {
    $complexBlocks = [
        [
            'id' => 'hero-block',
            'type' => 'hero',
            'content' => 'Main headline',
            'image' => 'hero.jpg',
            'cta' => ['text' => 'Learn More', 'url' => '/learn'],
        ],
        [
            'id' => 'features-block',
            'type' => 'features',
            'items' => [
                ['title' => 'Feature 1', 'description' => 'First feature'],
                ['title' => 'Feature 2', 'description' => 'Second feature'],
            ],
        ],
        [
            'id' => 'cta-block',
            'type' => 'call-to-action',
            'text' => 'Ready to get started?',
            'button' => ['text' => 'Get Started', 'url' => '/start'],
        ],
    ];
    
    $exportData = [
        'version' => '0.12.5',
        'exported_at' => now()->toIso8601String(),
        'posts' => [],
        'post_translations' => [],
        'categories' => [],
        'category_translations' => [],
        'tags' => [],
        'tag_translations' => [],
        'series' => [],
        'series_translations' => [],
        'users' => [],
        'user_translations' => [],
        'post_translation_categories' => [],
        'post_translation_tags' => [],
        'cms_pages' => [
            [
                'id' => 2,
                'slug' => 'complex-page',
                'template' => 'landing',
                'blocks' => $complexBlocks,
                'is_published' => true,
                'is_homepage' => false,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
        'cms_page_translations' => [
            [
                'id' => 3,
                'cms_page_id' => 2,
                'locale' => 'en',
                'slug' => 'complex-page-en',
                'title' => 'Complex Page',
                'meta_description' => 'Page with complex blocks',
                'content' => 'Content',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ],
    ];
    
    $importPath = storage_path('app/test-complex-cms.json');
    File::put($importPath, json_encode($exportData, JSON_PRETTY_PRINT));
    
    $this->artisan('blogr:import', ['file' => $importPath])
        ->assertExitCode(0);
    
    // Verify complex blocks were preserved exactly
    $page = \Happytodev\Blogr\Models\CmsPage::where('slug', 'complex-page')->first();
    expect($page)->not->toBeNull();
    expect($page->blocks)->toBe($complexBlocks);
    expect(count($page->blocks))->toBe(3);
    
    // Verify specific block structure
    $heroBlock = collect($page->blocks)->firstWhere('id', 'hero-block');
    expect($heroBlock['cta']['text'])->toBe('Learn More');
    
    $featuresBlock = collect($page->blocks)->firstWhere('id', 'features-block');
    expect(count($featuresBlock['items']))->toBe(2);
    
    // Cleanup
    File::delete($importPath);
});
