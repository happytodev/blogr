<?php
uses(Happytodev\Blogr\Tests\TestCase::class);


use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;

it('displays publication date on blog post card in index page', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    $publishedAt = now()->subDays(5);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => $publishedAt,
        'photo' => null,
        'default_locale' => 'en',
        'title' => 'Test Article with Date',
        'slug' => 'test-article-with-date',
        'content' => 'Test content',
    ]);
    
    $response = $this->get(route('blog.index'));
    
    $response->assertStatus(200);
    
    // Should display the formatted publication date using Carbon isoFormat
    $formattedDate = $publishedAt->locale('en')->isoFormat('LL'); // Ex: October 14, 2025
    $response->assertSee($formattedDate, false);
});

it('displays publication date on blog post card in category page', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create(['slug' => 'technology']);
    
    $publishedAt = now()->subDays(10);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => $publishedAt,
        'photo' => null,
        'default_locale' => 'en',
        'title' => 'Category Test Article',
        'slug' => 'category-test-article',
        'content' => 'Test content',
    ]);
    
    $response = $this->get(route('blog.category', ['categorySlug' => 'technology']));
    
    $response->assertStatus(200);
    
    // Should display the formatted publication date using Carbon isoFormat
    $formattedDate = $publishedAt->locale('en')->isoFormat('LL'); // Ex: October 14, 2025
    $response->assertSee($formattedDate, false);
});

it('displays publication date on blog post card in tag page', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $tag = \Happytodev\Blogr\Models\Tag::factory()->create(['slug' => 'laravel']);
    
    $publishedAt = now()->subDays(3);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => $publishedAt,
        'photo' => null,
        'default_locale' => 'en',
        'title' => 'Tag Test Article',
        'slug' => 'tag-test-article',
        'content' => 'Test content',
    ]);
    
    $post->tags()->attach($tag->id);
    
    $response = $this->get(route('blog.tag', ['tagSlug' => 'laravel']));
    
    $response->assertStatus(200);
    
    // Should display the formatted publication date using Carbon isoFormat
    $formattedDate = $publishedAt->locale('en')->isoFormat('LL'); // Ex: October 14, 2025
    $response->assertSee($formattedDate, false);
});
