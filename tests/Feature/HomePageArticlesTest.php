<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Hash;
use Happytodev\Blogr\Models\User;

beforeEach(function () {
    $this->author = User::create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->category = Category::create([
        'name' => 'Tech',
        'slug' => 'tech',
    ]);
});

test('homepage displays published articles without series', function () {
    // Create articles without series
    $post1 = BlogPost::create([
        'title' => 'Standalone Article 1',
        'slug' => 'standalone-1',
        'content' => 'Content here',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $post2 = BlogPost::create([
        'title' => 'Standalone Article 2',
        'slug' => 'standalone-2',
        'content' => 'Content here',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('Standalone Article 1');
    $response->assertSee('Standalone Article 2');
});

test('homepage displays articles from series', function () {
    $series = BlogSeries::create([
        'title' => 'Laravel Series',
        'slug' => 'laravel-series',
        'description' => 'Learn Laravel',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    $postInSeries = BlogPost::create([
        'title' => 'Article in Series',
        'slug' => 'article-in-series',
        'content' => 'Content here',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('Article in Series');
});

test('homepage displays both series and non-series articles', function () {
    $series = BlogSeries::create([
        'title' => 'Vue Series',
        'slug' => 'vue-series',
        'description' => 'Learn Vue',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Vue Article',
        'slug' => 'vue-article',
        'content' => 'Content',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Tutorial Article',
        'slug' => 'tutorial',
        'content' => 'Content',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('Vue Article');
    $response->assertSee('Tutorial Article');
});

test('homepage does not display unpublished articles', function () {
    BlogPost::create([
        'title' => 'Draft Article',
        'slug' => 'draft',
        'content' => 'Content',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => false,
    ]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertDontSee('Draft Article');
});
