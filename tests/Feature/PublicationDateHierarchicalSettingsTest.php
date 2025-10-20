<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    
    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
    ]);
});

it('displays dates everywhere when master toggle is enabled (default)', function () {
    // Default: all dates enabled
    expect(config('blogr.ui.dates.show_publication_date'))->toBeTrue();
    expect(config('blogr.ui.dates.show_publication_date_on_cards'))->toBeTrue();
    expect(config('blogr.ui.dates.show_publication_date_on_articles'))->toBeTrue();
    
    // Check card
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    $response->assertSee('October 15, 2024');
    
    // Check article
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    $response->assertSee('October 15, 2024');
});

it('hides all dates when master toggle is disabled', function () {
    config(['blogr.ui.dates.show_publication_date' => false]);
    config(['blogr.ui.dates.show_publication_date_on_cards' => true]); // Should be ignored
    config(['blogr.ui.dates.show_publication_date_on_articles' => true]); // Should be ignored
    
    // Check card - should not show date even if card setting is true
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    $response->assertSee('Test Post'); // Post visible
    $response->assertDontSee('October 15, 2024'); // Date hidden
    
    // Check article - should not show date even if article setting is true
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    $response->assertSee('Test Post');
    $response->assertDontSee('October 15, 2024');
});

it('hides dates only on cards when card setting is disabled', function () {
    config(['blogr.ui.dates.show_publication_date' => true]); // Master enabled
    config(['blogr.ui.dates.show_publication_date_on_cards' => false]); // Cards disabled
    config(['blogr.ui.dates.show_publication_date_on_articles' => true]); // Articles enabled
    
    // Check card - should not show date
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    $response->assertSee('Test Post');
    $response->assertDontSee('October 15, 2024');
    
    // Check article - should show date
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    $response->assertSee('October 15, 2024');
});

it('hides dates only on articles when article setting is disabled', function () {
    config(['blogr.ui.dates.show_publication_date' => true]); // Master enabled
    config(['blogr.ui.dates.show_publication_date_on_cards' => true]); // Cards enabled
    config(['blogr.ui.dates.show_publication_date_on_articles' => false]); // Articles disabled
    
    // Check card - should show date
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    $response->assertSee('October 15, 2024');
    
    // Check article - should not show date
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    $response->assertSee('Test Post');
    $response->assertDontSee('October 15, 2024');
});

it('respects hierarchical date settings on category pages', function () {
    $this->category->update(['slug' => 'tech']);
    
    config(['blogr.ui.dates.show_publication_date' => true]);
    config(['blogr.ui.dates.show_publication_date_on_cards' => false]);
    
    $response = $this->get(route('blog.category', ['categorySlug' => 'tech']));
    $response->assertSee('Test Post');
    $response->assertDontSee('October 15, 2024');
});

it('respects hierarchical date settings on tag pages', function () {
    $tag = \Happytodev\Blogr\Models\Tag::factory()->create(['slug' => 'laravel']);
    $this->post->tags()->attach($tag->id);
    
    config(['blogr.ui.dates.show_publication_date' => true]);
    config(['blogr.ui.dates.show_publication_date_on_cards' => false]);
    
    $response = $this->get(route('blog.tag', ['tagSlug' => 'laravel']));
    $response->assertSee('Test Post');
    $response->assertDontSee('October 15, 2024');
});
