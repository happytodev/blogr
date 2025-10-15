<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'slug' => 'johndoe',
        'avatar' => 'avatars/john.jpg',
    ]);

    $this->category = Category::factory()->create([
        'name' => 'Tech',
        'slug' => 'tech',
    ]);

    $this->post = BlogPost::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('setting to show hide author pseudo exists', function () {
    expect(config('blogr.display.show_author_pseudo'))->not->toBeNull();
});

test('setting to show hide author avatar thumbnail exists', function () {
    expect(config('blogr.display.show_author_avatar'))->not->toBeNull();
});

test('article card shows author pseudo when setting is enabled', function () {
    config(['blogr.display.show_author_pseudo' => true]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));

    $response->assertStatus(200);
    $response->assertSee('johndoe');
});

test('article card hides author pseudo when setting is disabled', function () {
    config(['blogr.display.show_author_pseudo' => false]);
    config(['blogr.author_profile.enabled' => false]); // Disable author profile to prevent slug in URL

    $response = $this->get(route('blog.index', ['locale' => 'en']));

    $response->assertStatus(200);
    $response->assertDontSee('johndoe');
});

test('article card shows author avatar when setting is enabled', function () {
    config(['blogr.display.show_author_avatar' => true]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));

    $response->assertStatus(200);
    $response->assertSee('avatars/john.jpg', false);
});

test('article card hides author avatar when setting is disabled', function () {
    config(['blogr.display.show_author_avatar' => false]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));

    $response->assertStatus(200);
    $response->assertDontSee('avatars/john.jpg', false);
});

test('article detail page shows author pseudo when setting is enabled', function () {
    config(['blogr.display.show_author_pseudo' => true]);

    $response = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => 'test-article',
    ]));

    $response->assertStatus(200);
    $response->assertSee('johndoe');
});

test('article detail page hides author pseudo when setting is disabled', function () {
    config(['blogr.display.show_author_pseudo' => false]);
    config(['blogr.display.show_author_avatar' => false]); // Disable both to hide author info in meta

    $response = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => 'test-article',
    ]));

    $response->assertStatus(200);
    // Check that author-info component is not in Post Meta section (not checking author-bio at bottom)
    $response->assertDontSee('<!-- Author Info -->');
});

test('article detail page shows author avatar when setting is enabled', function () {
    config(['blogr.display.show_author_avatar' => true]);

    $response = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => 'test-article',
    ]));

    $response->assertStatus(200);
    $response->assertSee('avatars/john.jpg', false);
});

test('article detail page hides author avatar when setting is disabled', function () {
    config(['blogr.display.show_author_avatar' => false]);
    config(['blogr.display.show_author_pseudo' => false]); // Disable both to hide author info in meta

    $response = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => 'test-article',
    ]));

    $response->assertStatus(200);
    // Check that author-info component is not in Post Meta section  
    $response->assertDontSee('<!-- Author Info -->');
});

test('author pseudo falls back to name when slug is empty', function () {
    $this->author->update(['slug' => null]);
    
    config(['blogr.display.show_author_pseudo' => true]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));

    $response->assertStatus(200);
    $response->assertSee('John Doe');
});
