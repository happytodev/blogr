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

    $instance->data['photo'] = 'blog-photos/real-uploaded-image.jpg';

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

test('regression_273_x_button_deletes_main_photo', function () {
    // Post has a photo
    $this->post->update(['photo' => 'blog-photos/existing-image.jpg']);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    // User clicked X on FilePond → null value (non-multiple FileUpload)
    $instance->data['photo'] = null;

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->post->refresh();
    expect($this->post->photo)->toBeNull();
});

test('regression_273_x_button_preserves_when_never_had_photo', function () {
    // Post has NO photo
    $this->post->update(['photo' => null]);

    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    // X button sends null on a post that never had a photo
    $instance->data['photo'] = null;

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAndPublish');
    $reflection->setAccessible(true);
    $reflection->invoke($instance);

    $this->post->refresh();
    // Should remain null (no corruption)
    expect($this->post->photo)->toBeNull();
});

test('regression_268_uuid_nested_livewire_format', function () {
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $instance = $component->instance();

    $instance->data['photo'] = 'blog-photos/main-image.jpg';

    $data = $instance->data;
    foreach ($data['translations'] as $key => $translation) {
        $data['translations'][$key]['photo'] = [
            '9a8b7c6d-1234-5678-9abc-def012345678' => [
                'Livewire\Features\SupportFileUploads\TemporaryUploadedFile' => '/tmp/fake-temp-file.jpg',
            ],
        ];
    }
    $instance->data = $data;

    $reflection = new ReflectionMethod(EditBlogPost::class, 'mutateFormDataBeforeSave');
    $reflection->setAccessible(true);
    $mutated = $reflection->invoke($instance, $data);

    expect($mutated['photo'] ?? null)->toBe('blog-photos/main-image.jpg');

    foreach ($mutated['translations'] ?? [] as $t) {
        $photo = $t['photo'] ?? null;
        expect($photo)->not->toBeNull();
        expect(is_array($photo) || is_string($photo))->toBeTrue();
    }
});
