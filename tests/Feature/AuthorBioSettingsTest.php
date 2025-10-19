<?php

namespace Happytodev\Blogr\Tests\Feature;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

class AuthorBioSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected BlogPost $post;

    /**
     * Define environment setup.
     * This is called BEFORE setUp() and is the right place to set config values
     * that affect the ServiceProvider boot process (like route registration).
     */
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Enable locales for these tests - must be set before routes are registered
        $app['config']->set('blogr.locales.enabled', true);
        $app['config']->set('blogr.locales.default', 'en');
        $app['config']->set('blogr.locales.available', ['en', 'fr']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create author with bio
        $this->author = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'slug' => 'john-doe',
            'bio' => [
                'en' => 'This is John bio in English',
                'fr' => 'Ceci est la bio de John en francais',
            ],
            'avatar' => 'avatars/john.jpg',
        ]);

        // Create category
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        // Create post
        $this->post = BlogPost::create([
            'user_id' => $this->author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Create translation
        $this->post->translations()->create([
            'locale' => 'en',
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'This is test content',
        ]);

        // Configure blogr - no need to set locales again, done in getEnvironmentSetUp
        config(['blogr.author_profile.enabled' => true]);
        config(['blogr.route.frontend.enabled' => true]);
        config(['blogr.route.prefix' => 'blog']);
    }

    public function test_author_bio_is_not_displayed_when_disabled()
    {
        // Disable author bio
        config(['blogr.author_bio.enabled' => false]);

        $response = $this->get('/en/blog/test-post');

        $response->assertStatus(200);
        $response->assertDontSee('About the author');
        $response->assertDontSee('This is John bio');
    }

    public function test_author_bio_is_displayed_when_enabled()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'bottom']);
        config(['blogr.author_bio.compact' => false]);

        $response = $this->get('/en/blog/test-post');

        $response->assertStatus(200);
        $response->assertSee('About the author');
        $response->assertSee('John Doe');
        $response->assertSee('This is John bio in English');
    }

    public function test_author_bio_uses_compact_version_when_configured()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.compact' => true]);

        $response = $this->get('/en/blog/test-post');

        $response->assertStatus(200);
        $response->assertSee('Written by');
        $response->assertDontSee('About the author');
    }

    public function test_author_bio_uses_full_version_when_compact_is_false()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.compact' => false]);

        $response = $this->get('/en/blog/test-post');

        $response->assertStatus(200);
        $response->assertSee('About the author');
        $response->assertDontSee('Written by');
    }

    public function test_author_bio_displays_at_top_when_position_is_top()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'top']);
        config(['blogr.author_bio.compact' => false]);

        $response = $this->get('/en/blog/test-post');
        $content = $response->getContent();

        $response->assertStatus(200);
        $response->assertSee('About the author');
        
        // Check that bio appears BEFORE the main article prose div
        $bioPosition = strpos($content, 'About the author');
        // Look for the prose div that wraps the main content
        $proseDiv = 'class="prose prose-lg dark:prose-invert';
        $contentPosition = strpos($content, $proseDiv);
        
        $this->assertNotFalse($bioPosition, 'Author bio should be present');
        $this->assertNotFalse($contentPosition, 'Prose content div should be present');
        $this->assertLessThan($contentPosition, $bioPosition, 'Bio should appear before prose content div');
    }

    public function test_author_bio_displays_at_bottom_when_position_is_bottom()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'bottom']);
        config(['blogr.author_bio.compact' => false]);

        $response = $this->get('/en/blog/test-post');
        $content = $response->getContent();

        $response->assertStatus(200);
        $response->assertSee('About the author');
        
        // Check that bio appears AFTER the article content
        $bioPosition = strpos($content, 'About the author');
        $contentPosition = strpos($content, 'This is test content');
        
        $this->assertNotFalse($bioPosition, 'Author bio should be present');
        $this->assertNotFalse($contentPosition, 'Content should be present');
        $this->assertGreaterThan($contentPosition, $bioPosition, 'Bio should appear after content');
    }

    public function test_author_bio_displays_at_both_positions_when_position_is_both()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'both']);
        config(['blogr.author_bio.compact' => false]);

        $response = $this->get('/en/blog/test-post');
        $content = $response->getContent();

        $response->assertStatus(200);
        
        // Count occurrences of "About the author" - should appear twice
        $occurrences = substr_count($content, 'About the author');
        $this->assertEquals(2, $occurrences, 'Bio should appear twice (top and bottom)');
    }

    public function test_author_bio_compact_displays_at_top_when_position_is_top()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'top']);
        config(['blogr.author_bio.compact' => true]);

        $response = $this->get('/en/blog/test-post');
        $content = $response->getContent();

        $response->assertStatus(200);
        $response->assertSee('Written by');
        
        // Check that compact bio appears BEFORE the main article prose div
        $bioPosition = strpos($content, 'Written by');
        // Look for the prose div that wraps the main content
        $proseDiv = 'class="prose prose-lg dark:prose-invert';
        $contentPosition = strpos($content, $proseDiv);
        
        $this->assertNotFalse($bioPosition, 'Compact bio should be present');
        $this->assertNotFalse($contentPosition, 'Prose content div should be present');
        $this->assertLessThan($contentPosition, $bioPosition, 'Compact bio should appear before prose content div');
    }

    public function test_author_bio_compact_displays_at_bottom_when_position_is_bottom()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'bottom']);
        config(['blogr.author_bio.compact' => true]);

        $response = $this->get('/en/blog/test-post');
        $content = $response->getContent();

        $response->assertStatus(200);
        $response->assertSee('Written by');
        
        // Check that compact bio appears AFTER the article content
        $bioPosition = strpos($content, 'Written by');
        $contentPosition = strpos($content, 'This is test content');
        
        $this->assertNotFalse($bioPosition, 'Compact bio should be present');
        $this->assertNotFalse($contentPosition, 'Content should be present');
        $this->assertGreaterThan($contentPosition, $bioPosition, 'Compact bio should appear after content');
    }

    public function test_author_bio_compact_displays_at_both_positions_when_position_is_both()
    {
        config(['blogr.author_bio.enabled' => true]);
        config(['blogr.author_bio.position' => 'both']);
        config(['blogr.author_bio.compact' => true]);

        $response = $this->get('/en/blog/test-post');
        $content = $response->getContent();

        $response->assertStatus(200);
        
        // Count occurrences of "Written by" - should appear twice
        $occurrences = substr_count($content, 'Written by');
        $this->assertEquals(2, $occurrences, 'Compact bio should appear twice (top and bottom)');
    }
}
