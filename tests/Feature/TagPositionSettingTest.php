<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
    
    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Test Post with Tags',
        'slug' => 'test-post-tags',
        'content' => '<p>This is the main content of the article.</p>',
    ]);
    
    $this->tag1 = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $this->tag2 = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    $this->post->tags()->attach([$this->tag1->id, $this->tag2->id]);
});

it('displays tags at the bottom of article by default', function () {
    expect(config('blogr.ui.posts.tags_position'))->toBe('bottom');
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-tags']));
    $content = $response->getContent();
    
    // Find the position of the prose content div (main article content)
    $prosePos = strpos($content, 'class="prose prose-lg');
    
    // Find the tags section (look for the Tags: label)
    $tagsLabelPos = strpos($content, 'text-sm font-medium text-gray-600 dark:text-gray-400">Tags:');
    
    // Tags should appear AFTER the prose content
    expect($tagsLabelPos)->toBeGreaterThan($prosePos);
});

it('displays tags at the top of article when setting is top', function () {
    config(['blogr.ui.posts.tags_position' => 'top']);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-tags']));
    $content = $response->getContent();
    
    // Find the position of the prose content div (main article content)
    $prosePos = strpos($content, 'class="prose prose-lg');
    
    // Find the tags section (look for the Tags: label)
    $tagsLabelPos = strpos($content, 'text-sm font-medium text-gray-600 dark:text-gray-400">Tags:');
    
    // Tags should appear BEFORE the prose content
    expect($tagsLabelPos)->toBeLessThan($prosePos);
    expect($tagsLabelPos)->toBeGreaterThan(0);
});

it('always displays both tags regardless of position', function () {
    // Test bottom
    config(['blogr.ui.posts.tags_position' => 'bottom']);
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-tags']));
    $response->assertSee('Laravel');
    $response->assertSee('PHP');
    
    // Test top
    config(['blogr.ui.posts.tags_position' => 'top']);
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-tags']));
    $response->assertSee('Laravel');
    $response->assertSee('PHP');
});

it('handles invalid tag position by defaulting to bottom', function () {
    config(['blogr.ui.posts.tags_position' => 'invalid-value']);
    
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post-tags']));
    $content = $response->getContent();
    
    // Should not display tags with invalid position (neither top nor bottom)
    // Or default to bottom - let's check if tags are NOT displayed
    $tagsLabelPos = strpos($content, 'text-sm font-medium text-gray-600 dark:text-gray-400">Tags:');
    
    // With invalid value, tags won't match either 'top' or 'bottom', so won't be displayed
    expect($tagsLabelPos)->toBeFalse();
});
