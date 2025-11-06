<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->category = Category::factory()->create();
});

it('renders markdown in author bio with bold and italic', function () {
    $user = User::factory()->create([
        'bio' => 'I am a **developer** and I love _Laravel_.',
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Should render markdown to HTML
    $response->assertSee('<strong>developer</strong>', false);
    $response->assertSee('<em>Laravel</em>', false);
});

it('renders markdown links in author bio', function () {
    $user = User::factory()->create([
        'bio' => 'Visit my [website](https://example.com) for more info.',
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Should render link
    $response->assertSee('<a href="https://example.com">website</a>', false);
});

it('renders markdown lists in author bio', function () {
    $user = User::factory()->create([
        'bio' => "My skills:\n- PHP\n- Laravel\n- Vue.js",
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Should render as list
    $response->assertSee('<ul>', false);
    $response->assertSee('<li>PHP</li>', false);
    $response->assertSee('<li>Laravel</li>', false);
    $response->assertSee('<li>Vue.js</li>', false);
});

it('sanitizes dangerous HTML in bio markdown', function () {
    $user = User::factory()->create([
        'bio' => 'Hello <script>alert("xss")</script> **world**',
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Should escape script tags but render markdown
    // Check the bio doesn't contain alert("xss")
    $response->assertDontSee('alert("xss")', false);
    $response->assertSee('<strong>world</strong>', false);
});

it('handles empty bio gracefully', function () {
    $user = User::factory()->create([
        'bio' => null,
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Should not fail
    $response->assertStatus(200);
});

it('renders markdown bio with array (multilingual support)', function () {
    $user = User::factory()->create([
        'bio' => [
            'en' => 'I am a **developer** working with _Laravel_.',
            'fr' => 'Je suis un **développeur** qui travaille avec _Laravel_.',
        ],
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    // Check that markdown is rendered for the default locale (en)
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    $response->assertSee('<strong>developer</strong>', false);
    $response->assertSee('<em>Laravel</em>', false);
});

it('preserves line breaks in markdown bio', function () {
    $user = User::factory()->create([
        'bio' => "Line 1\n\nLine 2",
    ]);
    
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Content',
    ]);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Should create paragraphs
    $response->assertSee('<p>Line 1</p>', false);
    $response->assertSee('<p>Line 2</p>', false);
});
