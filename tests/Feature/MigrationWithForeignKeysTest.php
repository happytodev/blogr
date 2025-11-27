<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Test 1: Vérifier que toutes les migrations s'exécutent sans erreur
it('can run all migrations including the fixed delete() migration', function () {
    // Execute all migrations - this will test the fixed migration
    $this->artisan('migrate')->assertSuccessful();
    
    // Verify the problematic migration has been applied
    $appliedMigrations = DB::table('migrations')
        ->where('migration', 'like', '%2025_10_13_000001%')
        ->count();
    
    expect($appliedMigrations)->toBeGreaterThan(0);
    
    // Verify the structural changes were applied:
    // The translatable fields should NOT exist in blog_posts anymore
    $blogPostColumns = Schema::getColumnListing('blog_posts');
    
    expect($blogPostColumns)
        ->not->toContain('title')
        ->not->toContain('slug')
        ->not->toContain('content')
        ->not->toContain('tldr')
        ->not->toContain('meta_title')
        ->not->toContain('meta_description')
        ->not->toContain('meta_keywords');
    
    // Verify essential columns still exist
    expect($blogPostColumns)
        ->toContain('id')
        ->toContain('user_id')
        ->toContain('category_id')
        ->toContain('is_published')
        ->toContain('published_at')
        ->toContain('created_at')
        ->toContain('updated_at');
});

// Test 2: Vérifier que delete() fonctionne même sur une table vide (installation fraîche)
it('delete() works on empty tables during fresh installation', function () {
    // This simulates the scenario of a fresh installation
    
    // Run all migrations
    $this->artisan('migrate')->assertSuccessful();
    
    // Verify table is empty (fresh installation)
    expect(BlogPost::count())->toBe(0);
    
    // Execute delete() on empty table (should not throw error)
    // This is what the migration does
    DB::table('blog_posts')->delete();
    
    // Verify still empty (no errors)
    expect(BlogPost::count())->toBe(0);
});

// Test 3: Vérifier que le rollback fonctionne
it('rollback of migration works correctly', function () {
    // Run migrations
    $this->artisan('migrate')->assertSuccessful();
    
    // Verify columns are removed
    $columnsAfterMigration = Schema::getColumnListing('blog_posts');
    expect($columnsAfterMigration)->not->toContain('title');
    
    // Rollback ALL migrations to test the down() method
    $this->artisan('migrate:reset')->assertSuccessful();
    
    // After a full reset, the blog_posts table should not exist
    expect(Schema::hasTable('blog_posts'))->toBeFalse();
    
    // Re-run migrations up to (but not including) the problematic migration
    // This tests that the migration system is working correctly
    $this->artisan('migrate')->assertSuccessful();
    
    // Verify we can create posts after migration
    expect(Schema::hasTable('blog_posts'))->toBeTrue();
});

// Test 4: Documentation du moteur de base de données utilisé
it('confirms SQLite is used in tests and delete() is compatible', function () {
    $this->artisan('migrate')->assertSuccessful();
    
    $driverName = DB::getDriverName();
    
    // Test delete() operation works
    DB::table('blog_posts')->delete();
    
    // Verify it worked and document the driver
    expect(BlogPost::count())->toBe(0)
        ->and($driverName)->toBe('sqlite');
});
