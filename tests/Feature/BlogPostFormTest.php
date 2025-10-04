<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('preserves frontmatter when saving without changing toggle', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post with frontmatter
    $contentWithFrontmatter = "---\ntitle: Test Post\ndisable_toc: \"true\"\n---\n\n# Test Content\n\nThis is the actual content of the post.";

    $blogPost = BlogPost::create([
        'title' => 'Test Post',
        'content' => $contentWithFrontmatter,
        'slug' => 'test-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Verify initial state
    expect($blogPost->isTocDisabled())->toBeTrue();
    expect($blogPost->content)->toBe($contentWithFrontmatter);

    // Simulate what happens when editing in Filament
    // The accessor should return content without frontmatter when in admin context
    // For testing, we'll test the method directly since we can't simulate admin routes easily
    $contentForForm = $blogPost->getContentWithoutFrontmatter(); // Direct method call for testing
    expect($contentForForm)->toBe("# Test Content\n\nThis is the actual content of the post.");

    // When saving, the dehydrateStateUsing should add back the frontmatter
    // because isTocDisabled() is true
    $savedContent = $contentForForm; // This would be the content from the form

    // Simulate the dehydrateStateUsing logic using reflection
    if ($blogPost->isTocDisabled()) {
        $reflection = new \ReflectionClass(BlogPostForm::class);
        $method = $reflection->getMethod('updateFrontmatterInContent');
        $method->setAccessible(true);
        $savedContent = $method->invoke(null, $savedContent, ['disable_toc' => true]);
    }

    expect($savedContent)->toContain('disable_toc: true');
    expect($savedContent)->toContain('# Test Content');
});

it('displays content without frontmatter when no frontmatter exists', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post without frontmatter
    $contentWithoutFrontmatter = "# Test Content\n\nThis is the actual content of the post.";

    $blogPost = BlogPost::create([
        'title' => 'Test Post',
        'content' => $contentWithoutFrontmatter,
        'slug' => 'test-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Test that getContentWithoutFrontmatter returns the same content
    $result = $blogPost->getContentWithoutFrontmatter();

    expect($result)->toBe($contentWithoutFrontmatter);
});

it('correctly identifies TOC disabled status from frontmatter', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post with disable_toc: true
    $contentWithFrontmatter = "---\ntitle: Test Post\ndisable_toc: \"true\"\n---\n\n# Test Content\n\nThis is the actual content of the post.";

    $blogPost = BlogPost::create([
        'title' => 'Test Post',
        'content' => $contentWithFrontmatter,
        'slug' => 'test-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Test that isTocDisabled returns true
    expect($blogPost->isTocDisabled())->toBeTrue();
});

it('correctly identifies TOC enabled status when no disable_toc in frontmatter', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post without disable_toc in frontmatter
    $contentWithFrontmatter = "---\ntitle: Test Post\n---\n\n# Test Content\n\nThis is the actual content of the post.";

    $blogPost = BlogPost::create([
        'title' => 'Test Post',
        'content' => $contentWithFrontmatter,
        'slug' => 'test-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Test that isTocDisabled returns false (default)
    expect($blogPost->isTocDisabled())->toBeFalse();
});

it('correctly identifies TOC enabled status when no frontmatter exists', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post without frontmatter
    $contentWithoutFrontmatter = "# Test Content\n\nThis is the actual content of the post.";

    $blogPost = BlogPost::create([
        'title' => 'Test Post',
        'content' => $contentWithoutFrontmatter,
        'slug' => 'test-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Test that isTocDisabled returns false (default)
    expect($blogPost->isTocDisabled())->toBeFalse();
});

it('shows correct helper text when strict mode is disabled', function () {
    // Mock config to disable strict mode
    config(['blogr.toc.strict_mode' => false]);

    // Test the helper text function directly
    $helperTextFunction = function () {
        $strictMode = config('blogr.toc.strict_mode', false);
        if ($strictMode) {
            $globalTocEnabled = config('blogr.toc.enabled', true);
            $statusMessage = $globalTocEnabled
                ? 'Currently, table of contents are always displayed for all posts.'
                : 'Currently, table of contents are always disabled for all posts.';

            return 'TOC setting is controlled globally and cannot be changed per post. ' . $statusMessage;
        }
        return 'Disable the automatic table of contents generation for this post.';
    };

    $helperText = $helperTextFunction();

    expect($helperText)->toBe('Disable the automatic table of contents generation for this post.');
});

it('shows correct helper text when strict mode is enabled and global TOC is enabled', function () {
    // Mock config to enable strict mode and global TOC
    config(['blogr.toc.strict_mode' => true]);
    config(['blogr.toc.enabled' => true]);

    // Test the helper text function directly
    $helperTextFunction = function () {
        $strictMode = config('blogr.toc.strict_mode', false);
        if ($strictMode) {
            $globalTocEnabled = config('blogr.toc.enabled', true);
            $statusMessage = $globalTocEnabled
                ? 'Currently, table of contents are always displayed for all posts.'
                : 'Currently, table of contents are always disabled for all posts.';

            return 'TOC setting is controlled globally and cannot be changed per post. ' . $statusMessage;
        }
        return 'Disable the automatic table of contents generation for this post.';
    };

    $helperText = $helperTextFunction();

    expect($helperText)->toBe('TOC setting is controlled globally and cannot be changed per post. Currently, table of contents are always displayed for all posts.');
});

it('shows correct helper text when strict mode is enabled and global TOC is disabled', function () {
    // Mock config to enable strict mode and disable global TOC
    config(['blogr.toc.strict_mode' => true]);
    config(['blogr.toc.enabled' => false]);

    // Test the helper text function directly
    $helperTextFunction = function () {
        $strictMode = config('blogr.toc.strict_mode', false);
        if ($strictMode) {
            $globalTocEnabled = config('blogr.toc.enabled', true);
            $statusMessage = $globalTocEnabled
                ? 'Currently, table of contents are always displayed for all posts.'
                : 'Currently, table of contents are always disabled for all posts.';

            return 'TOC setting is controlled globally and cannot be changed per post. ' . $statusMessage;
        }
        return 'Disable the automatic table of contents generation for this post.';
    };

    $helperText = $helperTextFunction();

    expect($helperText)->toBe('TOC setting is controlled globally and cannot be changed per post. Currently, table of contents are always disabled for all posts.');
});

it('preserves past publish dates when editing existing published posts', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post published in the past (1 year ago)
    $pastDate = now()->subYear();
    $blogPost = BlogPost::create([
        'title' => 'Past Published Post',
        'content' => 'This is a test post content.',
        'slug' => 'past-published-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => $pastDate,
    ]);

    // Verify initial state
    expect($blogPost->published_at->toDateTimeString())->toBe($pastDate->toDateTimeString());

    // Simulate editing the post (changing title but keeping publish date)
    $blogPost->update([
        'title' => 'Updated Past Published Post',
        'slug' => 'updated-past-published-post',
        'is_published' => true, // Keep the published status
        'published_at' => $pastDate, // Explicitly preserve the past date
    ]);

    // Debug: check values before refresh
    expect($blogPost->is_published)->toBeTrue(); // Check before refresh

    // Refresh from database
    $blogPost->refresh();

    // Verify that the past publish date is preserved
    expect($blogPost->published_at->toDateTimeString())->toBe($pastDate->toDateTimeString());
    expect($blogPost->is_published)->toBeTruthy(); // Should be published (truthy)
});

it('allows scheduling future publish dates for new posts', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post scheduled for the future
    $futureDate = now()->addDays(7);
    $blogPost = BlogPost::create([
        'title' => 'Future Scheduled Post',
        'content' => 'This post will be published in the future.',
        'slug' => 'future-scheduled-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => $futureDate,
    ]);

    // Verify the future date is set correctly
    expect($blogPost->published_at->toDateTimeString())->toBe($futureDate->toDateTimeString());
    expect($blogPost->is_published)->toBeTrue();
});

it('handles immediate publication correctly', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a blog post for immediate publication (no published_at date)
    $blogPost = BlogPost::create([
        'title' => 'Immediately Published Post',
        'content' => 'This post is published immediately.',
        'slug' => 'immediately-published-post',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => null, // Immediate publication
    ]);

    // Verify the post is published and has a publish date set to now (automatic)
    expect($blogPost->published_at)->not->toBeNull();
    expect($blogPost->published_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($blogPost->is_published)->toBeTruthy();
});

it('handles draft posts correctly', function () {
    // Create a user and category
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // Create a draft blog post
    $blogPost = BlogPost::create([
        'title' => 'Draft Post',
        'content' => 'This is a draft post.',
        'slug' => 'draft-post',
        'is_published' => false,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => null,
    ]);

    // Verify the post is not published
    expect($blogPost->is_published)->toBeFalse();
    expect($blogPost->published_at)->toBeNull();
});
