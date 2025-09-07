<?php

use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;
use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can instantiate all blog widgets', function () {
    // Test that all widget classes exist and can be instantiated
    expect(class_exists(BlogStatsOverview::class))->toBeTrue();
    expect(class_exists(RecentBlogPosts::class))->toBeTrue();
    expect(class_exists(ScheduledPosts::class))->toBeTrue();
    expect(class_exists(BlogPostsChart::class))->toBeTrue();
    expect(class_exists(BlogReadingStats::class))->toBeTrue();

    // Test widget instantiation
    $statsWidget = new BlogStatsOverview();
    $recentWidget = new RecentBlogPosts();
    $scheduledWidget = new ScheduledPosts();
    $chartWidget = new BlogPostsChart();
    $readingWidget = new BlogReadingStats();

    expect($statsWidget)->toBeInstanceOf(BlogStatsOverview::class);
    expect($recentWidget)->toBeInstanceOf(RecentBlogPosts::class);
    expect($scheduledWidget)->toBeInstanceOf(ScheduledPosts::class);
    expect($chartWidget)->toBeInstanceOf(BlogPostsChart::class);
    expect($readingWidget)->toBeInstanceOf(BlogReadingStats::class);
});

it('blog widgets have required methods', function () {
    $statsWidget = new BlogStatsOverview();
    $recentWidget = new RecentBlogPosts();
    $chartWidget = new BlogPostsChart();

    // Test that widgets have expected methods
    expect(method_exists($statsWidget, 'getStats'))->toBeTrue();
    expect(method_exists($recentWidget, 'table'))->toBeTrue();
    expect(method_exists($chartWidget, 'getData'))->toBeTrue();
});

it('widgets can query blog data', function () {
    // Create test data
    $user = User::factory()->create();
    $category = Category::factory()->create();

    // Create some blog posts
    BlogPost::create([
        'title' => 'Test Post 1',
        'content' => 'Test content',
        'slug' => 'test-post-1',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    BlogPost::create([
        'title' => 'Test Post 2',
        'content' => 'Test content 2',
        'slug' => 'test-post-2',
        'is_published' => false,
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    // Test that data exists
    expect(BlogPost::count())->toBe(2);
    expect(BlogPost::where('is_published', true)->count())->toBe(1);
    expect(BlogPost::where('is_published', false)->count())->toBe(1);
});
