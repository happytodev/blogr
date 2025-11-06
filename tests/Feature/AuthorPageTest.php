<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Hash;
use Happytodev\Blogr\Models\User;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->author = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'john-doe',
        'avatar' => 'avatars/john.jpg',
        'bio' => 'This is John Doe biography',
    ]);

    $this->category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    for ($i = 1; $i <= 3; $i++) {
        BlogPost::create([
            'title' => "Test Post {$i}",
            'slug' => "test-post-{$i}",
            'content' => "Content for post {$i}",
            'user_id' => $this->author->id,
            'category_id' => $this->category->id,
            'is_published' => true,
            'published_at' => now()->subDays(3 - $i),
        ]);
    }
});

test('author page is accessible with slug when locales are disabled', function () {
    config(['blogr.author_profile.enabled' => true]);
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    
    $response = get('/blog/author/john-doe');
    
    $response->assertStatus(200);
    $response->assertSee('John Doe');
    $response->assertSee('This is John Doe biography');
});

test('author page displays author posts', function () {
    config(['blogr.author_profile.enabled' => true]);
    config(['blogr.locales.enabled' => false]);
    
    $response = get('/blog/author/john-doe');
    
    $response->assertStatus(200);
    $response->assertSee('Test Post 1');
    $response->assertSee('Test Post 2');
    $response->assertSee('Test Post 3');
});

test('author page returns 404 for non-existent author', function () {
    config(['blogr.author_profile.enabled' => true]);
    config(['blogr.locales.enabled' => false]);
    
    $response = get('/blog/author/non-existent-author');
    
    $response->assertStatus(404);
});

test('author page returns 404 when author profile feature is disabled', function () {
    config(['blogr.author_profile.enabled' => false]);
    config(['blogr.locales.enabled' => false]);
    
    $response = get('/blog/author/john-doe');
    
    $response->assertStatus(404);
});

test('author page only shows published posts', function () {
    config(['blogr.author_profile.enabled' => true]);
    config(['blogr.locales.enabled' => false]);
    
    BlogPost::create([
        'title' => 'Unpublished Post',
        'slug' => 'unpublished-post',
        'content' => 'This should not be visible',
        'user_id' => $this->author->id,
        'category_id' => $this->category->id,
        'is_published' => false,
        'published_at' => null,
    ]);
    
    $response = get('/blog/author/john-doe');
    
    $response->assertStatus(200);
    $response->assertDontSee('Unpublished Post');
});

test('author name in article is clickable and leads to author page', function () {
    config(['blogr.author_profile.enabled' => true]);
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);

    // Use author and post created in beforeEach
    $response = get('/blog/test-post-1');

    $response->assertStatus(200);
    $response->assertSee('blog/author/john-doe', false); // Check that author link is present
    $response->assertSee('John Doe');
});