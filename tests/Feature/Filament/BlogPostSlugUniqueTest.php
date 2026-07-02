<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->session(['errors' => new ViewErrorBag]);

    Category::factory()->create();
});

test('regression_258_slug_unique_validation', function () {
    $categoryId = Category::first()->id;

    // Create an existing post with a specific slug
    BlogPost::create([
        'default_locale' => 'en',
        'user_id' => auth()->id(),
        'category_id' => $categoryId,
        'title' => 'Existing Post',
        'slug' => 'existing-slug',
        'content' => 'Existing content',
    ]);

    // Try to create a new post with the same slug via the Filament form
    Livewire::test(CreateBlogPost::class)
        ->fillForm([
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => 'New Post',
                    'slug' => 'existing-slug',
                    'content' => 'New content',
                ],
            ],
            'category_id' => $categoryId,
            'default_locale' => 'en',
            'is_published' => false,
        ])
        ->call('create')
        ->assertHasFormErrors(['translations.0.slug' => 'unique']);
});
