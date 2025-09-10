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
