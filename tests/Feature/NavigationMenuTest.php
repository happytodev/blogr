<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CategoryTranslation;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\get;

beforeEach(function () {
    Config::set('app.locale', 'en');
    Config::set('blogr.ui.navigation.menu_items', []);
});

test('navigation renders with empty menu items', function () {
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
});

test('navigation renders external link correctly', function () {
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'external',
            'label' => 'GitHub',
            'url' => 'https://github.com',
            'target' => '_blank',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('GitHub')
        ->assertSee('https://github.com');
});

test('navigation renders blog home link correctly', function () {
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'blog',
            'label' => 'Blog Home',
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('Blog Home');
});

test('navigation renders category link with correct route parameters', function () {
    $category = Category::factory()->create();
    $translation = CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'name' => 'Tutorials',
        'slug' => 'tutorials',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'category',
            'label' => 'Tutorials',
            'category_id' => $category->id,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('Tutorials');
    
    // Verify the URL uses categorySlug parameter (not slug)
    $expectedUrl = route('blog.category', ['locale' => 'en', 'categorySlug' => 'tutorials']);
    $response->assertSee($expectedUrl, false);
});

test('navigation handles category without translation gracefully', function () {
    $category = Category::factory()->create();
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'category',
            'label' => 'Category Without Translation',
            'category_id' => $category->id,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('Category Without Translation');
});

test('navigation handles non-existent category gracefully', function () {
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'category',
            'label' => 'Non Existent Category',
            'category_id' => 99999,
            'target' => '_self',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('Non Existent Category');
});

test('navigation supports multiple menu items', function () {
    $category = Category::factory()->create();
    CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'name' => 'News',
        'slug' => 'news',
    ]);
    
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'blog',
            'label' => 'Home',
            'target' => '_self',
        ],
        [
            'type' => 'category',
            'label' => 'News',
            'category_id' => $category->id,
            'target' => '_self',
        ],
        [
            'type' => 'external',
            'label' => 'About',
            'url' => 'https://example.com/about',
            'target' => '_blank',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200)
        ->assertSee('Home')
        ->assertSee('News')
        ->assertSee('About');
});

test('navigation renders mobile menu with same items', function () {
    Config::set('blogr.ui.navigation.menu_items', [
        [
            'type' => 'external',
            'label' => 'GitHub',
            'url' => 'https://github.com',
            'target' => '_blank',
        ],
    ]);
    
    $response = get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    
    // Check that GitHub appears multiple times (desktop + mobile)
    $content = $response->getContent();
    expect(substr_count($content, 'GitHub'))->toBeGreaterThanOrEqual(2);
});
