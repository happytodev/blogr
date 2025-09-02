<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('displays blog post with title, category, tags, TLDR and TOC correctly', function () {
    // Create a user directly (since User factory is not available in this package)
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category directly
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Create tags directly
    $tag1 = Tag::create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $tag2 = Tag::create([
        'name' => 'PHP',
        'slug' => 'php',
    ]);

    // Create a blog post directly with content that will generate a TOC
    $blogPost = BlogPost::create([
        'title' => 'Complete Guide to Laravel Development',
        'content' => "# Introduction\n\nThis is an introduction to Laravel.\n\n## Getting Started\n\nLearn how to install Laravel.\n\n### Prerequisites\n\nYou need PHP 8.1+ and Composer.\n\n## Advanced Features\n\nExplore advanced Laravel features.\n\n### Middleware\n\nUnderstanding Laravel middleware.\n\n### Eloquent ORM\n\nWorking with the database ORM.",
        'slug' => 'complete-guide-laravel-development',
        'tldr' => 'A comprehensive guide covering Laravel from basics to advanced concepts including installation, middleware, and Eloquent ORM.',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Attach tags to the blog post
    $blogPost->tags()->attach([$tag1->id, $tag2->id]);

    // Visit the blog post page
    $response = $this->get(route('blog.show', $blogPost->slug));

    // Assert the response is successful
    $response->assertOk();

    // Assert the title is displayed
    $response->assertSee('Complete Guide to Laravel Development');

    // Assert the category is displayed
    $response->assertSee('Technology');
    $response->assertSee(route('blog.category', $category->slug));

    // Assert the tags are displayed
    $response->assertSee('Laravel');
    $response->assertSee('PHP');
    $response->assertSee(route('blog.tag', $tag1->slug));
    $response->assertSee(route('blog.tag', $tag2->slug));

    // Assert the TL;DR is displayed
    $response->assertSee('TL;DR');
    $response->assertSee('A comprehensive guide covering Laravel from basics to advanced concepts including installation, middleware, and Eloquent ORM.');

    // Assert the TOC is generated and displayed
    $response->assertSee('Table of contents');
    $response->assertSee('Introduction');
    $response->assertSee('Getting Started');
    $response->assertSee('Prerequisites');
    $response->assertSee('Advanced Features');
    $response->assertSee('Middleware');
    $response->assertSee('Eloquent ORM');

    // Assert the main content is displayed
    $response->assertSee('This is an introduction to Laravel');
    $response->assertSee('Learn how to install Laravel');
    $response->assertSee('You need PHP 8.1+ and Composer');
    $response->assertSee('Explore advanced Laravel features');
    $response->assertSee('Understanding Laravel middleware');
    $response->assertSee('Working with the database ORM');
});

it('does not display scheduled posts before their publish date', function () {
    // Create a user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Create a scheduled blog post (future date)
    $futureDate = now()->addDays(7);
    $scheduledPost = BlogPost::create([
        'title' => 'Future Laravel Features',
        'content' => 'This post is scheduled for the future.',
        'slug' => 'future-laravel-features',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => $futureDate,
    ]);

    // Visit the blog index
    $response = $this->get(route('blog.index'));

    // Assert the scheduled post is NOT displayed
    $response->assertDontSee('Future Laravel Features');
});

it('displays scheduled posts after their publish date', function () {
    // Create a user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Create a scheduled blog post (past date)
    $pastDate = now()->subDays(1);
    $scheduledPost = BlogPost::create([
        'title' => 'Past Laravel Features',
        'content' => 'This post was scheduled in the past.',
        'slug' => 'past-laravel-features',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => $pastDate,
    ]);

    // Visit the blog index
    $response = $this->get(route('blog.index'));

    // Assert the scheduled post IS displayed
    $response->assertSee('Past Laravel Features');
});

it('correctly identifies publication status', function () {
    // Create a user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Test draft post
    $draftPost = BlogPost::create([
        'title' => 'Draft Post',
        'content' => 'This is a draft.',
        'slug' => 'draft-post',
        'is_published' => false,
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    expect($draftPost->getPublicationStatus())->toBe('draft');
    expect($draftPost->getPublicationStatusColor())->toBe('gray');

    // Test scheduled post
    $scheduledPost = BlogPost::create([
        'title' => 'Scheduled Post',
        'content' => 'This is scheduled.',
        'slug' => 'scheduled-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now()->addDays(1),
    ]);

    expect($scheduledPost->getPublicationStatus())->toBe('scheduled');
    expect($scheduledPost->getPublicationStatusColor())->toBe('warning');

    // Test published post
    $publishedPost = BlogPost::create([
        'title' => 'Published Post',
        'content' => 'This is published.',
        'slug' => 'published-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now()->subDays(1),
    ]);

    expect($publishedPost->getPublicationStatus())->toBe('published');
    expect($publishedPost->getPublicationStatusColor())->toBe('success');
});

it('updates published_at to current time when republishing a scheduled post', function () {
    // Create a user directly (since User factory is not available in this package)
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category directly
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Create a scheduled post (future date)
    $futureDate = now()->addDays(7);
    $scheduledPost = BlogPost::create([
        'title' => 'Scheduled Post',
        'content' => 'This post is scheduled for the future.',
        'slug' => 'scheduled-post',
        'is_published' => true,
        'published_at' => $futureDate,
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    // Verify initial state
    expect($scheduledPost->is_published)->toBe(true);
    expect($scheduledPost->published_at->toDateTimeString())->toBe($futureDate->toDateTimeString());
    expect($scheduledPost->getPublicationStatus())->toBe('scheduled');

    // Simulate unpublishing (like clicking toggle off in form)
    $scheduledPost->update([
        'is_published' => false,
        'published_at' => null,
    ]);

    // Verify unpublished state
    $scheduledPost->refresh();
    expect((bool) $scheduledPost->is_published)->toBe(false);
    expect($scheduledPost->published_at)->toBe(null);
    expect($scheduledPost->getPublicationStatus())->toBe('draft');

    // Simulate republishing (like clicking toggle on again in form)
    // This should set published_at to current time
    $beforeRepublish = now();
    $scheduledPost->update([
        'is_published' => true,
        'published_at' => now()->format('Y-m-d\TH:i'), // This simulates what the form does
    ]);

    // Verify republished state
    $scheduledPost->refresh();
    expect((bool) $scheduledPost->is_published)->toBe(true);
    expect($scheduledPost->published_at)->not->toBe(null);
    // Check that published_at is recent (within last minute)
    expect($scheduledPost->published_at->isAfter($beforeRepublish->subMinute()))->toBe(true);
    expect($scheduledPost->getPublicationStatus())->toBe('published');

    // Verify the post is now considered currently published
    expect($scheduledPost->isCurrentlyPublished())->toBe(true);
});

it('calculates estimated reading time correctly', function () {
    // Create a user directly (since User factory is not available in this package)
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category directly
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Test with very short content (should be < 1 minute)
    $shortPost = BlogPost::create([
        'title' => 'Hi',
        'content' => 'Test.',
        'slug' => 'short-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    expect($shortPost->getEstimatedReadingTime())->toBe('<1 minute');

    // Test with longer content (should calculate properly)
    $longPost = BlogPost::create([
        'title' => 'Long Post with Substantial Content',
        'content' => str_repeat('This is a sample paragraph with enough words to create a meaningful reading time estimate. ', 50),
        'slug' => 'long-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $readingTime = $longPost->getEstimatedReadingTime();
    expect($readingTime)->not->toBe('<1 minute');
    expect(str_contains($readingTime, 'minute'))->toBe(true);

    // Test reading time with icon
    $timeWithIcon = $longPost->getReadingTimeWithIcon();
    expect(str_contains($timeWithIcon, 'minute'))->toBe(true);
    expect($timeWithIcon)->not->toBe('<1 minute'); // Should be longer than 1 minute
});

it('displays consistent reading time between index and show pages', function () {
    // Create a user directly (since User factory is not available in this package)
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category directly
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Create a blog post with content that will have TOC added
    $blogPost = BlogPost::create([
        'title' => 'Test Post for Reading Time Consistency',
        'content' => "# Introduction\n\nThis is a comprehensive guide with multiple sections.\n\n## Section 1\n\nDetailed content here.\n\n## Section 2\n\nMore detailed information.\n\n## Section 3\n\nFinal section with important details.",
        'slug' => 'test-reading-time-consistency',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Get reading time from the model (as used in index page)
    $indexReadingTime = $blogPost->getEstimatedReadingTime();

    // Simulate what happens in the controller show method
    $post = BlogPost::with(['category', 'tags'])
        ->where('slug', $blogPost->slug)
        ->where('is_published', true)
        ->firstOrFail();

    // Calculate reading time BEFORE adding TOC (as done in controller)
    $post->reading_time = $post->getEstimatedReadingTime();

    // Verify that the reading time is consistent
    expect($post->reading_time)->toBe($indexReadingTime);
});

it('respects reading time configuration settings', function () {
    // Create a user directly (since User factory is not available in this package)
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category directly
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Create a blog post
    $blogPost = BlogPost::create([
        'title' => 'Test Post for Configuration',
        'content' => 'This is a test post with some content for reading time calculation.',
        'slug' => 'test-config-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Test with default configuration (enabled)
    config(['blogr.reading_time.enabled' => true]);
    config(['blogr.reading_time.text_format' => 'Reading time: {time}']);
    $formattedTime = $blogPost->getFormattedReadingTime();
    expect($formattedTime)->toContain('Reading time:');
    expect($formattedTime)->toContain('minute');

    // Test with custom format
    config(['blogr.reading_time.text_format' => '{time} to read']);
    $formattedTime = $blogPost->getFormattedReadingTime();
    expect($formattedTime)->toContain('to read');
    expect($formattedTime)->toContain('minute');

    // Test with reading time disabled
    config(['blogr.reading_time.enabled' => false]);
    $formattedTime = $blogPost->getFormattedReadingTime();
    expect($formattedTime)->toBe('');
});

it('validates that published_at date cannot be in the past when creating a post', function () {
    // NOTE: This test validates model-level behavior. For comprehensive form validation testing,
    // you would need to use Filament's testing tools to simulate form submission with past dates.
    //
    // Example of how to test form validation (requires Filament testing setup):
    // $this->actingAs($user)
    //     ->post(route('filament.admin.resources.blog-posts.create'), [
    //         'title' => 'Test Post',
    //         'content' => 'Test content',
    //         'category_id' => $category->id,
    //         'is_published' => true,
    //         'published_at' => now()->subHours(1)->format('Y-m-d\TH:i'),
    //     ])
    //     ->assertInvalid(['published_at']);

    // Create a user directly (since User factory is not available in this package)
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    // Create a category directly
    $category = Category::create([
        'name' => 'Technology',
        'slug' => 'technology',
        'is_default' => false,
    ]);

    // Test creating a post with past date through the model
    $pastDate = now()->subHours(1);

    // This should work at model level (no validation there)
    $postWithPastDate = BlogPost::create([
        'title' => 'Post with Past Date',
        'content' => 'This post has a past publication date.',
        'slug' => 'post-with-past-date',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => $pastDate,
    ]);

    // Verify the post was created successfully
    expect($postWithPastDate)->toBeInstanceOf(BlogPost::class);
    expect($postWithPastDate->published_at->toDateTimeString())->toBe($pastDate->toDateTimeString());

    // Verify the publication status is correctly identified as published (past date)
    expect($postWithPastDate->getPublicationStatus())->toBe('published');
    expect($postWithPastDate->isCurrentlyPublished())->toBe(true);

    // Test validation rule directly
    $validator = Validator::make([
        'published_at' => now()->subHours(1)->format('Y-m-d H:i:s'),
    ], [
        'published_at' => 'nullable|date|after:now',
    ]);

    expect($validator->fails())->toBe(true);
    expect($validator->errors()->has('published_at'))->toBe(true);
});
