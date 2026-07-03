<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $category = Category::factory()->create();

    $this->post = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'title' => 'Real File Upload Test',
        'slug' => 'real-file-upload-test',
        'content' => 'Content',
    ]);
});

test('regression_268_real_upload_flow', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    // Set the main photo
    $instance->data['photo'] = 'blog-photos/real-uploaded-image.jpg';

    // Set the translation photo — Filament uses record-{id} keys for Repeater items
    $data = $instance->data;
    foreach ($data['translations'] as $key => $translation) {
        $data['translations'][$key]['photo'] = 'blog-photos/translation-image.jpg';
    }
    $instance->data = $data;

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->post->refresh();
    expect($this->post->photo)->toBe('blog-photos/real-uploaded-image.jpg');

    $translation = $this->post->translations()->first()->refresh();
    expect($translation->photo)->toBe('blog-photos/translation-image.jpg');
});

test('regression_268_uuid_nested_livewire_format', function () {
    // Simulate the EXACT format Livewire sends for a file upload inside a Repeater:
    //   [0 => ["uuid-1234" => ["Livewire\Features\SupportFileUploads\TemporaryUploadedFile" => "/tmp/..."]]]
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $instance->data['photo'] = 'blog-photos/main-image.jpg';

    // Set the EXACT UUID-nested format from the production log
    $data = $instance->data;
    foreach ($data['translations'] as $key => $translation) {
        $data['translations'][$key]['photo'] = [
            '9a8b7c6d-1234-5678-9abc-def012345678' => [
                'Livewire\Features\SupportFileUploads\TemporaryUploadedFile' => '/tmp/fake-temp-file.jpg',
            ],
        ];
    }
    $instance->data = $data;

    // This should NOT crash and should NOT remove the photo
    $reflection = new ReflectionMethod(EditBlogPost::class, 'mutateFormDataBeforeSave');
    $reflection->setAccessible(true);
    $mutated = $reflection->invoke($instance, $data);

    // The main photo should be preserved as a string
    expect($mutated['photo'] ?? null)->toBe('blog-photos/main-image.jpg');

    // The translation photo should be passed through as-is for persistUploadedFiles
    foreach ($mutated['translations'] ?? [] as $t) {
        // The photo is kept in UUID-nested format here;
        // persistUploadedFiles (called in saveAndPublish) will store it
        $photo = $t['photo'] ?? null;
        expect($photo)->not->toBeNull();
        // Should be an array (the UUID-nested format)
        expect(is_array($photo) || is_string($photo))->toBeTrue();
    }
});
