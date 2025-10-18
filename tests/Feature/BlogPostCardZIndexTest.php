<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Workbench\App\Models\User;

test('blog post card content section has proper z-index to keep links clickable', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    $post = BlogPost::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.index', ['locale' => 'en']));
    $response->assertStatus(200);

    // The content div (p-6) should have relative and z-10 classes to ensure
    // links remain clickable when hovering from the bottom of the card
    $html = $response->getContent();
    
    // Check for "relative z-10" in the content wrapper  (p-6 flex-grow flex flex-col)
    expect($html)->toContain('p-6 flex-grow flex flex-col relative z-10');
});
