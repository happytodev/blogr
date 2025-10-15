<?php

namespace Happytodev\Blogr\Tests\Feature;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorPageLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure blogr for tests
        config(['blogr.author_profile.enabled' => true]);
        config(['blogr.locales.enabled' => true]);
        config(['blogr.locales.default' => 'en']);
        config(['blogr.reading_time.enabled' => true]);
        config(['blogr.reading_speed.words_per_minute' => 200]);
    }

    /** @test */
    public function author_page_uses_same_container_width_as_homepage()
    {
        $user = $this->createUser(['slug' => 'john-doe']);
        
        $response = $this->get('/en/author/john-doe');
        
        $response->assertStatus(200);
        // Should use max-w-7xl like homepage, not max-w-6xl
        $response->assertSee('max-w-7xl', false);
    }

    /** @test */
    public function author_bio_is_displayed_above_articles()
    {
        $user = $this->createUser([
            'slug' => 'john-doe',
            'name' => 'John Doe',
            'bio' => 'Expert developer and writer',
        ]);
        
        $category = Category::factory()->create();
        BlogPost::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        $response = $this->get('/en/author/john-doe');
        
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'John Doe',
            'Expert developer and writer',
            'Articles by', // Section separator
        ]);
    }

    /** @test */
    public function author_page_cards_display_reading_time()
    {
        $user = $this->createUser(['slug' => 'john-doe']);
        $category = Category::factory()->create();
        
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        // Create translation with content to calculate reading time
        $post->translations()->create([
            'locale' => 'en',
            'title' => 'Test Post Title',
            'slug' => 'test-post',
            'content' => str_repeat('word ', 1000), // ~1000 words = 5 min at 200 wpm
            'tldr' => 'Test TLDR',
        ]);
        
        $response = $this->get('/en/author/john-doe');
        
        $response->assertStatus(200);
        // Should display reading time badge calculated from translation content
        $response->assertSee('min', false); // Reading time should be present
    }

    /** @test */
    public function author_page_cards_display_category_badge_consistently()
    {
        $user = $this->createUser(['slug' => 'john-doe']);
        $category = Category::factory()->create(['name' => 'Technology']);
        
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        $response = $this->get('/en/author/john-doe');
        
        $response->assertStatus(200);
        // Category should be in badge format, not inline link
        $response->assertSee('Technology', false);
        $response->assertSee('bg-white/90', false); // Category badge styling
    }

    /** @test */
    public function post_images_are_clickable_and_link_to_post()
    {
        $user = $this->createUser(['slug' => 'john-doe']);
        $category = Category::factory()->create();
        
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
            'photo' => 'blog-photos/test.jpg',
        ]);
        
        $translation = $post->translations()->create([
            'locale' => 'en',
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
        ]);
        
        $response = $this->get('/en/author/john-doe');
        
        $response->assertStatus(200);
        // Image should be wrapped in <a> tag linking to post
        $response->assertSee('<a href', false);
        $response->assertSee('test-post', false);
    }

    /** @test */
    public function author_avatar_is_clickable_and_links_to_author_page()
    {
        $author = $this->createUser(['slug' => 'john-doe', 'name' => 'John Doe']);
        $category = Category::factory()->create();
        
        $post = BlogPost::factory()->create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        $response = $this->get('/en');
        
        $response->assertStatus(200);
        // Author info should link to author page
        $response->assertSee('john-doe', false);
    }

    protected function createUser(array $attributes = [])
    {
        $userModel = config('auth.providers.users.model');
        
        return $userModel::factory()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ], $attributes));
    }
}
