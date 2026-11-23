<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/*
 * Regression test: #374 — the position an editor sees in the form. It used to be
 * shown as "Custom position..." with an empty number field, so the number was lost
 * on the next save. The custom field now carries it.
 */

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->session(['errors' => new ViewErrorBag]);

    $category = Category::factory()->create();
    $series = BlogSeries::create(['slug' => 'amber-fossils', 'is_published' => true]);
    $series->translations()->create([
        'locale' => 'en',
        'title' => 'Amber Fossils',
        'slug' => 'amber-fossils',
        'description' => 'A series.',
    ]);

    $this->post = BlogPost::create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'blog_series_id' => $series->id,
        'series_position' => 2,
        'default_locale' => 'en',
        'is_published' => false,
    ]);

    $this->post->translations()->create([
        'locale' => 'en',
        'title' => 'Day thirty three',
        'slug' => 'day-thirty-three',
        'content' => 'Some body.',
    ]);
});

it('shows the stored position in the custom field', function () {
    Livewire::test(EditBlogPost::class, ['record' => $this->post->id])
        ->assertFormSet([
            'series_position' => 'custom',
            'series_position_custom' => 2,
        ]);
});
