<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

beforeEach(function () {
    // Clean up any existing export files
    Storage::fake('local');
});

it('can export all blogr data to json', function () {
    // Create test data
    $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'is_default' => true]);
    $tag = Tag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);
    
    $post = BlogPost::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'published_at' => now(),
        'user_id' => 1,
        'category_id' => $category->id,
    ]);
    
    // Run export command
    $this->artisan('blogr:export')
        ->expectsOutput('✅ Blogr data exported successfully')
        ->assertExitCode(0);
    
    // Check export file was created
    $exportPath = storage_path('app/blogr-exports');
    expect(File::exists($exportPath))->toBeTrue();
    
    $files = File::files($exportPath);
    expect(count($files))->toBeGreaterThan(0);
});

it('export includes version and timestamp', function () {
    $this->artisan('blogr:export');
    
    $exportPath = storage_path('app/blogr-exports');
    $files = File::files($exportPath);
    
    if (count($files) > 0) {
        $latestFile = collect($files)->sortByDesc(fn ($file) => $file->getMTime())->first();
        $content = File::get($latestFile->getPathname());
        $data = json_decode($content, true);
        
        expect($data)->toHaveKeys(['version', 'exported_at', 'posts', 'series', 'categories', 'tags']);
        expect($data['version'])->toBeString();
        expect($data['exported_at'])->toBeString();
    }
});

it('can export to custom path', function () {
    $customPath = storage_path('app/custom-backup.json');
    
    $this->artisan('blogr:export', ['--output' => $customPath])
        ->assertExitCode(0);
    
    if (File::exists($customPath)) {
        expect(File::exists($customPath))->toBeTrue();
        File::delete($customPath);
    }
});

it('export includes relationships data', function () {
    // Create post with relationships
    $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_default' => true]);
    $tag = Tag::create(['name' => 'Tag', 'slug' => 'tag']);
    
    $post = BlogPost::create([
        'title' => 'Post with Relations',
        'slug' => 'post-relations',
        'content' => 'Content',
        'published_at' => now(),
        'user_id' => 1,
        'category_id' => $category->id,
    ]);
    
    $this->artisan('blogr:export');
    
    $exportPath = storage_path('app/blogr-exports');
    $files = File::files($exportPath);
    
    if (count($files) > 0) {
        $latestFile = collect($files)->sortByDesc(fn ($file) => $file->getMTime())->first();
        $content = File::get($latestFile->getPathname());
        $data = json_decode($content, true);
        
        expect($data['categories'])->toBeArray();
        expect($data['tags'])->toBeArray();
    }
});

it('export includes all translation data', function () {
    // Create test data with translations
    $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'is_default' => true]);
    $tag = Tag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);
    $series = BlogSeries::create(['name' => 'Test Series', 'slug' => 'test-series']);
    
    $post = BlogPost::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'published_at' => now(),
        'user_id' => 1,
        'category_id' => $category->id,
    ]);
    
    // Run export command
    $this->artisan('blogr:export')
         ->expectsOutputToContain('Blogr data exported successfully')
         ->assertSuccessful();
    
    // Get the latest export file
    $files = File::files(storage_path('app/blogr-exports'));
    expect(count($files))->toBeGreaterThan(0);
    
    $latestFile = collect($files)->sortByDesc(fn ($file) => $file->getMTime())->first();
    $content = File::get($latestFile->getPathname());
    $data = json_decode($content, true);
    
    // Verify all translation sections are present
    expect($data)->toHaveKey('post_translations');
    expect($data)->toHaveKey('series_translations');
    expect($data)->toHaveKey('category_translations');
    expect($data)->toHaveKey('tag_translations');
    expect($data)->toHaveKey('user_translations');
    expect($data)->toHaveKey('post_translation_categories');
    expect($data)->toHaveKey('post_translation_tags');
    
    // Verify they are arrays
    expect($data['post_translations'])->toBeArray();
    expect($data['series_translations'])->toBeArray();
    expect($data['category_translations'])->toBeArray();
    expect($data['tag_translations'])->toBeArray();
    expect($data['user_translations'])->toBeArray();
    expect($data['post_translation_categories'])->toBeArray();
    expect($data['post_translation_tags'])->toBeArray();
});

it('can export and import all data including translations', function () {
    // Create test data with translations
    $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'is_default' => true]);
    $tag = Tag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);
    $series = BlogSeries::create(['name' => 'Test Series', 'slug' => 'test-series']);
    
    $post = BlogPost::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'published_at' => now(),
        'user_id' => 1,
        'category_id' => $category->id,
    ]);
    
    // Run export command
    $this->artisan('blogr:export')
         ->expectsOutputToContain('Blogr data exported successfully')
         ->assertSuccessful();
    
    // Get the latest export file
    $files = File::files(storage_path('app/blogr-exports'));
    expect(count($files))->toBeGreaterThan(0);
    
    $latestFile = collect($files)->sortByDesc(fn ($file) => $file->getMTime())->first();
    $exportPath = $latestFile->getPathname();
    
    // Verify the export file contains all expected sections
    $content = File::get($exportPath);
    $data = json_decode($content, true);
    
    // Verify all translation sections are present and contain data
    expect($data)->toHaveKey('post_translations');
    expect($data)->toHaveKey('series_translations');
    expect($data)->toHaveKey('category_translations');
    expect($data)->toHaveKey('tag_translations');
    expect($data)->toHaveKey('user_translations');
    expect($data)->toHaveKey('post_translation_categories');
    expect($data)->toHaveKey('post_translation_tags');
    
    // Verify they are arrays
    expect($data['post_translations'])->toBeArray();
    expect($data['series_translations'])->toBeArray();
    expect($data['category_translations'])->toBeArray();
    expect($data['tag_translations'])->toBeArray();
    expect($data['user_translations'])->toBeArray();
    expect($data['post_translation_categories'])->toBeArray();
    expect($data['post_translation_tags'])->toBeArray();
});

it('export includes translation photos in media files', function () {
    // Create category first
    $category = Category::create(['name' => 'Test', 'slug' => 'test', 'is_default' => true]);
    
    // Create post 
    $post = BlogPost::create([
        'slug' => 'test-post',
        'published_at' => now(),
        'user_id' => 1,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'content' => 'Test content',
        'photo' => 'blog-photos/main-photo.jpg',
    ]);
    
    // Add a French translation with photo
    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Test Post FR',
        'slug' => 'test-post-fr',
        'content' => 'Contenu FR',
        'photo' => 'blog-photos/translation-photo.jpg',
    ]);
    
    // Create series
    $series = BlogSeries::create([
        'slug' => 'test-series',
        'published_at' => now(),
        'photo' => 'blog-photos/main-series-photo.jpg',
    ]);
    
    $series->translations()->create([
        'locale' => 'en',
        'title' => 'Test Series EN',
        'slug' => 'test-series-en',
        'description' => 'Description EN',
    ]);
    
    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Test Series FR',
        'slug' => 'test-series-fr',
        'description' => 'Description FR',
        'photo' => 'blog-photos/series-translation-photo.jpg',
    ]);
    
    // Export
    $exportService = app(\Happytodev\Blogr\Services\BlogrExportService::class);
    $data = $exportService->export(['include_media' => true]);
    
    // Verify media files include all photos (main + translations)
    expect($data)->toHaveKey('media_files');
    expect($data['media_files'])->toContain('blog-photos/main-photo.jpg');
    expect($data['media_files'])->toContain('blog-photos/translation-photo.jpg');
    expect($data['media_files'])->toContain('blog-photos/main-series-photo.jpg');
    expect($data['media_files'])->toContain('blog-photos/series-translation-photo.jpg');
});
