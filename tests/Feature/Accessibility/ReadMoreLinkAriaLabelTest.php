<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.route.frontend.enabled' => true]);
    config(['blogr.ui.dates.show_publication_date' => false]);
    config(['blogr.display.show_author_pseudo' => false]);
    config(['blogr.display.show_author_avatar' => false]);
    config(['blogr.reading_time.enabled' => false]);
});

test('feature_read_more_link_has_aria_label', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();
    $post = BlogPost::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $postTranslation = $post->translations()->first();
    $postTranslation->update(['title' => 'Test Post Title']);

    $html = View::make('blogr::components.blog-post-card', [
        'post' => $post,
        'currentLocale' => 'en',
    ])->render();

    expect($html)
        ->toContain('aria-label')
        ->toContain('Read more about');
});
