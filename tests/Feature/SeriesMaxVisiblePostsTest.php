<?php

use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    config()->set('blogr.series.max_visible_posts', 3);
    config()->set('blogr.route.prefix', 'blog');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('saves and retrieves max_visible_posts setting', function () {
    $this->actingAs($this->admin);

    Livewire::test(BlogrSettings::class)
        ->fillForm([
            'series_max_visible_posts' => 7,
        ])
        ->call('save');

    expect(config('blogr.series.max_visible_posts'))->toBe(7);
});

it('shows series max visible posts field in settings', function () {
    $this->actingAs($this->admin);

    $response = $this->get(BlogrSettings::getUrl());

    $response->assertStatus(200);
    $response->assertSee('Max visible posts');
});

it('limite correctement le nombre d\'articles visibles dans une série', function () {
    $series = BlogSeries::factory()->create();
    $category = Category::factory()->create();

    for ($i = 1; $i <= 5; $i++) {
        BlogPost::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $category->id,
            'blog_series_id' => $series->id,
            'series_position' => $i,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    config()->set('blogr.series.max_visible_posts', 3);
    $max = config('blogr.series.max_visible_posts', 10);
    $count = $series->posts()->published()->count();
    $extraCount = max(0, $count - $max);

    expect($extraCount)->toBe(2);
});

it('shows all posts when max_visible_posts is higher than post count', function () {
    $series = BlogSeries::factory()->create();
    $category = Category::factory()->create();

    for ($i = 1; $i <= 3; $i++) {
        BlogPost::factory()->create([
            'user_id' => $this->admin->id,
            'category_id' => $category->id,
            'blog_series_id' => $series->id,
            'series_position' => $i,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    $max = config('blogr.series.max_visible_posts', 10);
    $count = $series->posts()->published()->count();
    $extraCount = max(0, $count - $max);

    expect($extraCount)->toBe(0);
});
