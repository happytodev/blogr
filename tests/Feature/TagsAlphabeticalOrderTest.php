<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays tags in alphabetical order on blog index page', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'title' => 'Test Post with Tags',
        'content' => 'This post has multiple tags.',
        'slug' => 'test-post-tags',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $tagZebra = Tag::factory()->create(['name' => 'Zebra', 'slug' => 'zebra']);
    $tagApple = Tag::factory()->create(['name' => 'Apple', 'slug' => 'apple']);
    $tagMango = Tag::factory()->create(['name' => 'Mango', 'slug' => 'mango']);
    $tagBanana = Tag::factory()->create(['name' => 'Banana', 'slug' => 'banana']);

    $post->tags()->attach([$tagZebra->id, $tagApple->id, $tagMango->id, $tagBanana->id]);

    $response = $this->get(route('blog.index'));
    $response->assertOk();

    $content = $response->getContent();

    $applePos = strpos($content, '#Apple');
    $bananaPos = strpos($content, '#Banana');
    $mangoPos = strpos($content, '#Mango');

    expect($applePos)->toBeLessThan($bananaPos)
        ->and($bananaPos)->toBeLessThan($mangoPos);
});

it('displays tags in alphabetical order on blog post detail page', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $post = BlogPost::create([
        'title' => 'Test Post Detail Tags',
        'content' => 'This post has multiple tags in detail page.',
        'slug' => 'test-post-detail-tags',
        'is_published' => true,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $tagZebra = Tag::factory()->create(['name' => 'Zebra', 'slug' => 'zebra-detail']);
    $tagApple = Tag::factory()->create(['name' => 'Apple', 'slug' => 'apple-detail']);
    $tagMango = Tag::factory()->create(['name' => 'Mango', 'slug' => 'mango-detail']);
    $tagBanana = Tag::factory()->create(['name' => 'Banana', 'slug' => 'banana-detail']);

    $post->tags()->attach([$tagZebra->id, $tagApple->id, $tagMango->id, $tagBanana->id]);

    $response = $this->get(route('blog.show', $post->slug));
    $response->assertOk();

    $content = $response->getContent();

    $applePos = strpos($content, '#Apple');
    $bananaPos = strpos($content, '#Banana');
    $mangoPos = strpos($content, '#Mango');
    $zebraPos = strpos($content, '#Zebra');

    expect($applePos)->toBeLessThan($bananaPos)
        ->and($bananaPos)->toBeLessThan($mangoPos)
        ->and($mangoPos)->toBeLessThan($zebraPos);
});
