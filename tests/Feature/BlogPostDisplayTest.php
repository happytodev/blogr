<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
