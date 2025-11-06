<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



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

it('does not display stray ">" character when publication dates are disabled', function () {
    // Disable publication dates
    config(['blogr.ui.dates.show_publication_date' => false]);
    
    // Visit the article page
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => 'test-post']));
    
    // Ensure the post is still visible
    $response->assertSee('Test Post');
    
    // Ensure the date is not displayed
    $response->assertDontSee('October 15, 2024');
    
    // Most importantly: ensure no stray ">" character appears in the meta section
    // This checks that the HTML output doesn't contain malformed closing tags
    $content = $response->getContent();
    
    // Check that the meta div doesn't start with ">" or contain stray ">" characters
    // Look for the meta section pattern and ensure it doesn't have leading ">"
    preg_match('/<div class="flex flex-wrap items-center gap-4[^"]*">([^<]*)/', $content, $matches);
    
    if (isset($matches[1])) {
        // The first content of the meta div should not start with ">"
        expect($matches[1])->not()->toStartWith('>');
        
        // Also check that there are no stray ">" characters in the meta section
        expect($matches[1])->not()->toContain('>&');
    }
});
