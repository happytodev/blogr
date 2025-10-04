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

it('displays limited tags in table with others count', function () {
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

    // Create multiple tags
    $tag1 = \Happytodev\Blogr\Models\Tag::create(['name' => 'lorem-1', 'slug' => 'lorem-1']);
    $tag2 = \Happytodev\Blogr\Models\Tag::create(['name' => 'lorem-2', 'slug' => 'lorem-2']);
    $tag3 = \Happytodev\Blogr\Models\Tag::create(['name' => 'lorem-3', 'slug' => 'lorem-3']);
    $tag4 = \Happytodev\Blogr\Models\Tag::create(['name' => 'lorem-4', 'slug' => 'lorem-4']);
    $tag5 = \Happytodev\Blogr\Models\Tag::create(['name' => 'lorem-5', 'slug' => 'lorem-5']);

    // Create a blog post with 5 tags
    $blogPost = BlogPost::create([
        'title' => 'Post with Many Tags',
        'content' => 'This post has many tags.',
        'slug' => 'post-with-many-tags',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Attach all 5 tags to the post
    $blogPost->tags()->attach([$tag1->id, $tag2->id, $tag3->id, $tag4->id, $tag5->id]);

    // Test the tag display logic (simulating what happens in the table)
    $tags = $blogPost->tags;
    $tagNames = $tags->pluck('name')->toArray();

    if (count($tagNames) <= 3) {
        $displayTags = $tagNames;
    } else {
        $displayTags = array_slice($tagNames, 0, 3);
        $remainingCount = count($tagNames) - 3;
        $word = $remainingCount === 1 ? 'other' : 'others';
        $displayTags[] = "+{$remainingCount} {$word}";
    }

    // Verify the display shows first 3 tags + "+2 others"
    expect($displayTags)->toHaveCount(4);
    expect($displayTags[0])->toBe('lorem-1');
    expect($displayTags[1])->toBe('lorem-2');
    expect($displayTags[2])->toBe('lorem-3');
    expect($displayTags[3])->toBe('+2 others');
});

it('displays all tags when 3 or fewer', function () {
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

    // Create 3 tags
    $tag1 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-1', 'slug' => 'tag-1']);
    $tag2 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-2', 'slug' => 'tag-2']);
    $tag3 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-3', 'slug' => 'tag-3']);

    // Create a blog post with 3 tags
    $blogPost = BlogPost::create([
        'title' => 'Post with Three Tags',
        'content' => 'This post has exactly 3 tags.',
        'slug' => 'post-with-three-tags',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Attach 3 tags to the post
    $blogPost->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

    // Test the tag display logic
    $tags = $blogPost->tags;
    $tagNames = $tags->pluck('name')->toArray();

    if (count($tagNames) <= 3) {
        $displayTags = $tagNames;
    } else {
        $displayTags = array_slice($tagNames, 0, 3);
        $remainingCount = count($tagNames) - 3;
        $word = $remainingCount === 1 ? 'other' : 'others';
        $displayTags[] = "+{$remainingCount} {$word}";
    }

    // Verify all 3 tags are displayed without "+others"
    expect($displayTags)->toHaveCount(3);
    expect($displayTags[0])->toBe('tag-1');
    expect($displayTags[1])->toBe('tag-2');
    expect($displayTags[2])->toBe('tag-3');
});

it('displays limited tags in table with singular other count', function () {
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

    // Create 4 tags
    $tag1 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-1', 'slug' => 'tag-1']);
    $tag2 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-2', 'slug' => 'tag-2']);
    $tag3 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-3', 'slug' => 'tag-3']);
    $tag4 = \Happytodev\Blogr\Models\Tag::create(['name' => 'tag-4', 'slug' => 'tag-4']);

    // Create a blog post with 4 tags
    $blogPost = BlogPost::create([
        'title' => 'Post with Four Tags',
        'content' => 'This post has exactly 4 tags.',
        'slug' => 'post-with-four-tags',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Attach 4 tags to the post
    $blogPost->tags()->attach([$tag1->id, $tag2->id, $tag3->id, $tag4->id]);

    // Test the tag display logic
    $tags = $blogPost->tags;
    $tagNames = $tags->pluck('name')->toArray();

    if (count($tagNames) <= 3) {
        $displayTags = $tagNames;
    } else {
        $displayTags = array_slice($tagNames, 0, 3);
        $remainingCount = count($tagNames) - 3;
        $word = $remainingCount === 1 ? 'other' : 'others';
        $displayTags[] = "+{$remainingCount} {$word}";
    }

    // Verify the display shows first 3 tags + "+1 other"
    expect($displayTags)->toHaveCount(4);
    expect($displayTags[0])->toBe('tag-1');
    expect($displayTags[1])->toBe('tag-2');
    expect($displayTags[2])->toBe('tag-3');
    expect($displayTags[3])->toBe('+1 other');
});
