<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

it('displays publication date on blog cards when setting is enabled by default', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
    ]);
    
    // Default setting should be enabled (true)
    expect(config('blogr.ui.dates.show_publication_date'))->toBeTrue();
    expect(config('blogr.ui.dates.show_publication_date_on_cards'))->toBeTrue();
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('October 15, 2024');
});

it('hides publication date on blog cards when setting is disabled', function () {
    Storage::fake('public');
    
    // Disable the setting
    config(['blogr.ui.dates.show_publication_date' => true]);
    config(['blogr.ui.dates.show_publication_date_on_cards' => false]);
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Test Post Hidden Date',
        'slug' => 'test-post-hidden-date',
        'content' => 'Test content',
    ]);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    
    // Should display the post title but not the date
    $response->assertSee('Test Post Hidden Date');
    $response->assertDontSee('October 15, 2024');
});

it('respects show_publication_date setting on category pages', function () {
    Storage::fake('public');
    
    config(['blogr.ui.dates.show_publication_date' => true]);
    config(['blogr.ui.dates.show_publication_date_on_cards' => false]);
    
    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'tech']);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Category Test Post',
        'slug' => 'category-test-post',
        'content' => 'Test content',
    ]);
    
    $response = $this->get(route('blog.category', ['categorySlug' => 'tech']));
    
    $response->assertStatus(200);
    $response->assertSee('Category Test Post');
    $response->assertDontSee('October 15, 2024');
});

it('respects show_publication_date setting on tag pages', function () {
    Storage::fake('public');
    
    config(['blogr.ui.dates.show_publication_date' => true]);
    config(['blogr.ui.dates.show_publication_date_on_cards' => false]);
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $tag = \Happytodev\Blogr\Models\Tag::factory()->create(['slug' => 'laravel']);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Tag Test Post',
        'slug' => 'tag-test-post',
        'content' => 'Test content',
    ]);
    
    $post->tags()->attach($tag->id);
    
    $response = $this->get(route('blog.tag', ['tagSlug' => 'laravel']));
    
    $response->assertStatus(200);
    $response->assertSee('Tag Test Post');
    $response->assertDontSee('October 15, 2024');
});
