<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\TagTranslation;
use Happytodev\Blogr\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    // Create tags with translations
    $tagZebra = Tag::factory()->create(['name' => 'Zebra', 'slug' => 'zebra']);
    TagTranslation::create(['tag_id' => $tagZebra->id, 'locale' => 'en', 'name' => 'Zebra', 'slug' => 'zebra']);
    
    $tagApple = Tag::factory()->create(['name' => 'Apple', 'slug' => 'apple']);
    TagTranslation::create(['tag_id' => $tagApple->id, 'locale' => 'en', 'name' => 'Apple', 'slug' => 'apple']);
    
    $tagMango = Tag::factory()->create(['name' => 'Mango', 'slug' => 'mango']);
    TagTranslation::create(['tag_id' => $tagMango->id, 'locale' => 'en', 'name' => 'Mango', 'slug' => 'mango']);
    
    $tagBanana = Tag::factory()->create(['name' => 'Banana', 'slug' => 'banana']);
    TagTranslation::create(['tag_id' => $tagBanana->id, 'locale' => 'en', 'name' => 'Banana', 'slug' => 'banana']);

    $post->tags()->attach([$tagZebra->id, $tagApple->id, $tagMango->id, $tagBanana->id]);

    $response = $this->get(route('blog.index'));
    $response->assertOk();

    $content = $response->getContent();
    
    // We only display 3 tags with take(3), so we need to check the first 3 alphabetically
    // Expected order: Apple, Banana, Mango (Zebra won't be displayed)

    $applePos = strpos($content, '#Apple');
    $bananaPos = strpos($content, '#Banana');
    $mangoPos = strpos($content, '#Mango');

    expect($applePos)->not->toBeFalse()
        ->and($bananaPos)->not->toBeFalse()
        ->and($mangoPos)->not->toBeFalse()
        ->and($applePos)->toBeLessThan($bananaPos)
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

    // Create tags with translations
    $tagZebra = Tag::factory()->create(['name' => 'Zebra', 'slug' => 'zebra-detail']);
    TagTranslation::create(['tag_id' => $tagZebra->id, 'locale' => 'en', 'name' => 'Zebra', 'slug' => 'zebra-detail']);
    
    $tagApple = Tag::factory()->create(['name' => 'Apple', 'slug' => 'apple-detail']);
    TagTranslation::create(['tag_id' => $tagApple->id, 'locale' => 'en', 'name' => 'Apple', 'slug' => 'apple-detail']);
    
    $tagMango = Tag::factory()->create(['name' => 'Mango', 'slug' => 'mango-detail']);
    TagTranslation::create(['tag_id' => $tagMango->id, 'locale' => 'en', 'name' => 'Mango', 'slug' => 'mango-detail']);
    
    $tagBanana = Tag::factory()->create(['name' => 'Banana', 'slug' => 'banana-detail']);
    TagTranslation::create(['tag_id' => $tagBanana->id, 'locale' => 'en', 'name' => 'Banana', 'slug' => 'banana-detail']);

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

it('keeps the tags relation available for filters', function () {
    expect((new BlogPost())->tags())->toBeInstanceOf(BelongsToMany::class);
});
