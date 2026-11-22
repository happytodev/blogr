<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/*
 * Regression test: #365 — the admin refused a slug that another locale already
 * used, although the public URL carries the locale. The rule is now scoped to
 * the item's locale; a duplicate inside the same locale is still refused.
 */

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->session(['errors' => new ViewErrorBag]);

    $this->category = Category::factory()->create();

    $existing = BlogPost::create([
        'default_locale' => 'en',
        'user_id' => $admin->id,
        'category_id' => $this->category->id,
    ]);

    $existing->translations()->create([
        'locale' => 'en',
        'title' => 'Existing Post',
        'slug' => 'existing-slug',
        'content' => 'Existing content',
    ]);
});

test('the same slug is accepted in another locale', function () {
    Livewire::test(CreateBlogPost::class)
        ->fillForm([
            'translations' => [
                [
                    'locale' => 'fr',
                    'title' => 'Nouvel article',
                    'slug' => 'existing-slug',
                    'content' => 'Nouveau contenu',
                ],
            ],
            'category_id' => $this->category->id,
            'default_locale' => 'fr',
            'is_published' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(BlogPostTranslation::where('locale', 'fr')->where('slug', 'existing-slug')->exists())->toBeTrue();
});

test('a duplicate inside the same locale is still refused', function () {
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
            'category_id' => $this->category->id,
            'default_locale' => 'en',
            'is_published' => false,
        ])
        ->call('create')
        ->assertHasFormErrors(['translations.0.slug' => 'unique']);
});
