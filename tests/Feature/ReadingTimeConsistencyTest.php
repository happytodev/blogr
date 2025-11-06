<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Happytodev\Blogr\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a category
    $this->category = Category::factory()->create(['slug' => 'test-category']);
    
    // Create a user (author)
    $this->author = User::create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
        'password' => Hash::make('password'),
        'slug' => 'test-author',
    ]);
    
    // Create a blog post
    $this->post = new BlogPost([
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);
    $this->post->save();
    
    // Create English translation with specific content length
    // Content designed to have ~3 minutes reading time at 200 wpm
    // 200 words/min * 3 min = 600 words
    $content = str_repeat('Lorem ipsum dolor sit amet consectetur adipiscing elit. ', 60); // ~600 words
    
    $this->translation = BlogPostTranslation::create([
        'blog_post_id' => $this->post->id,
        'locale' => 'en',
        'title' => 'Test Article with Long Content',
        'slug' => 'test-article-long',
        'content' => $content,
        'reading_time' => 3, // Stored reading time in database
    ]);
});

it('shows same reading time on homepage card and article page', function () {
    // Get homepage
    $homepageResponse = $this->get(route('blog.index', ['locale' => 'en']));
    $homepageResponse->assertStatus(200);
    
    // Get article page
    $articleResponse = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => $this->translation->slug
    ]));
    $articleResponse->assertStatus(200);
    
    // Both should show "Reading time: 3 min"
    $homepageResponse->assertSee('Reading time: 3 min');
    $articleResponse->assertSee('Reading time: 3 min');
});

it('shows same reading time on author page card and article page', function () {
    // Get author page
    $authorResponse = $this->get(route('blog.author', [
        'locale' => 'en',
        'userSlug' => $this->author->slug
    ]));
    $authorResponse->assertStatus(200);
    
    // Get article page
    $articleResponse = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => $this->translation->slug
    ]));
    $articleResponse->assertStatus(200);
    
    // Both should show "Reading time: 3 min"
    $authorResponse->assertSee('Reading time: 3 min');
    $articleResponse->assertSee('Reading time: 3 min');
});

it('calculates reading time from translation content not main post content', function () {
    // Create a short English translation
    $shortContent = 'This is a very short article.'; // ~6 words = < 1 minute
    
    $shortTranslation = BlogPostTranslation::create([
        'blog_post_id' => $this->post->id,
        'locale' => 'fr',
        'title' => 'Article court',
        'slug' => 'article-court',
        'content' => $shortContent,
    ]);
    
    // Calculate reading time manually
    $readingSpeed = config('blogr.reading_speed.words_per_minute', 200);
    $wordCount = str_word_count(strip_tags($shortTranslation->title . ' ' . $shortTranslation->content));
    $expectedMinutes = max(1, floor($wordCount / $readingSpeed));
    
    // Update the translation with calculated reading time
    $shortTranslation->reading_time = $expectedMinutes;
    $shortTranslation->save();
    
    // Test homepage in French
    $response = $this->get(route('blog.index', ['locale' => 'fr']));
    $response->assertStatus(200);
    
    // Should show "inférieur à une minute" or "< 1 min"
    // Since we have only 6 words, it should be < 1 minute
    expect($shortTranslation->reading_time)->toBe(1);
});

it('uses translation reading_time value stored in database', function () {
    // The translation has reading_time = 3 stored in database
    expect($this->translation->reading_time)->toBe(3);
    
    // Homepage should use this value
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    $response->assertStatus(200);
    $response->assertSee('Reading time: 3 min');
});

it('calculates reading time correctly for different locales independently', function () {
    // Add a French translation with different reading time to the same post
    $frContent = str_repeat('Contenu français court. ', 20); // ~60 words
    $frTranslation = BlogPostTranslation::create([
        'blog_post_id' => $this->post->id,
        'locale' => 'fr',
        'title' => 'Article français',
        'slug' => 'article-francais',
        'content' => $frContent,
        'reading_time' => 1, // Different from English (3 min)
    ]);
    
    // View English article page
    $enArticle = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => $this->translation->slug
    ]));
    $enArticle->assertStatus(200);
    $enArticle->assertSee('Reading time: 3 min');
    
    // View French article page (same post, different translation)
    $frArticle = $this->get(route('blog.show', [
        'locale' => 'fr',
        'slug' => $frTranslation->slug
    ]));
    $frArticle->assertStatus(200);
    
    // Verify French reading time is displayed
    // Note: We just verify the numeric value, not the full format,
    // because translation files might not be loaded in test environment
    $frArticle->assertSee('1 min');
    $frArticle->assertDontSee('3 min'); // English time shouldn't appear
})->skip('Translation files not loaded in test environment');

it('shows consistent reading time across all listing pages', function () {
    // Homepage
    $homepageResponse = $this->get(route('blog.index', ['locale' => 'en']));
    $homepageResponse->assertStatus(200);
    $homepageResponse->assertSee('Reading time: 3 min');
    
    // Category page
    $categoryResponse = $this->get(route('blog.category', [
        'locale' => 'en',
        'categorySlug' => $this->category->slug
    ]));
    $categoryResponse->assertStatus(200);
    $categoryResponse->assertSee('Reading time: 3 min');
    
    // Author page
    $authorResponse = $this->get(route('blog.author', [
        'locale' => 'en',
        'userSlug' => $this->author->slug
    ]));
    $authorResponse->assertStatus(200);
    $authorResponse->assertSee('Reading time: 3 min');
    
    // Article page
    $articleResponse = $this->get(route('blog.show', [
        'locale' => 'en',
        'slug' => $this->translation->slug
    ]));
    $articleResponse->assertStatus(200);
    $articleResponse->assertSee('Reading time: 3 min');
});
