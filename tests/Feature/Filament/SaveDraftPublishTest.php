<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\VersioningService;
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
        'title' => 'Published Post',
        'slug' => 'published-post',
        'content' => 'Content',
    ]);
});

test('regression_262_save_as_draft_preserves_is_published_toggle', function () {
    // Toggle is_published to false via Livewire and click "Save Draft"
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);
    $component->set('data.is_published', false);
    $component->set('data.published_at', null);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $component->assertSet('data.is_published', false);

    // The post should now be unpublished in the database
    $this->post->refresh();
    expect($this->post->is_published)->toBeFalse();
});

test('regression_262_save_as_draft_on_published_keeps_published', function () {
    // Click "Save Draft" without changing is_published
    $component = Livewire::test(EditBlogPost::class, ['record' => $this->post->id]);

    $reflection = new ReflectionMethod(EditBlogPost::class, 'saveAsDraft');
    $reflection->setAccessible(true);
    $reflection->invoke($component->instance());

    $component->assertSet('data.is_published', true);

    // The post should remain published
    $this->post->refresh();
    expect($this->post->is_published)->toBeTrue();
});
