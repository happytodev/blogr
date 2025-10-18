<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\Category;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

test('post displays toc when display_toc is true', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'display_toc' => true,
    ]);

    expect($post->shouldDisplayToc())->toBeTrue();
});

test('post does not display toc when display_toc is false', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'display_toc' => false,
    ]);

    expect($post->shouldDisplayToc())->toBeFalse();
});
