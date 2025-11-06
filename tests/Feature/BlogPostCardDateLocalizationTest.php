<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Carbon\Carbon;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.available' => ['en', 'fr', 'es']]);
    config(['blogr.locales.default' => 'en']);
});

it('displays date in English format on English blog index', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    // Create the post with a past publication date
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15), // Past date
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'English Post',
        'slug' => 'english-post',
        'content' => 'Test content in English',
    ]);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    
    // Should display English title
    $response->assertSee('English Post');
    
    // Should display date in English format using Carbon isoFormat('LL')
    // Format LL for EN: "October 15, 2024"
    $response->assertSee('October 15, 2024');
});

it('displays date with full month name using Carbon isoFormat', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $category = Category::factory()->create();
    
    // Create post in English
    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => Carbon::create(2024, 10, 15),
        'default_locale' => 'en',
        'photo' => null,
        'title' => 'Test Post for Date Format',
        'slug' => 'test-post-date-format',
        'content' => 'Testing date formats',
    ]);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    
    // Verify the date is formatted with full month name (Carbon isoFormat LL style)
    // Not the short format like "Oct 15, 2024"
    $response->assertSee('October'); // Full month name
    $response->assertSee('2024');    // Year
    $response->assertSee('15');      // Day
});
