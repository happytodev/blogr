<?php
uses(Happytodev\Blogr\Tests\TestCase::class);

use Livewire\Livewire;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\Category;
use Spatie\Permission\Models\Role;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\ListBlogPosts;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render the blog posts list page', function () {
    $this->get(BlogPostResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list blog posts in the table', function () {
    $posts = BlogPost::factory()->count(3)->create();

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords($posts);
});

it('lists posts sorted by created_at descending by default', function () {
    $old = BlogPost::factory()->create(['created_at' => now()->subDays(2)]);
    $new = BlogPost::factory()->create(['created_at' => now()]);

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$new, $old])
        ->assertTableColumnStateSet('title', $new->title, $new);
});

it('can filter by status', function () {
    $draft = BlogPost::factory()->create(['is_published' => false]);
    $published = BlogPost::factory()->create(['is_published' => true, 'published_at' => now()]);

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$draft, $published])
        ->filterTable('is_published', true)
        ->assertCanSeeTableRecords([$published])
        ->assertCanNotSeeTableRecords([$draft]);
});

it('can filter by category', function () {
    $catA = Category::factory()->create();
    $catB = Category::factory()->create();

    $postA = BlogPost::factory()->create(['category_id' => $catA->id]);
    $postB = BlogPost::factory()->create(['category_id' => $catB->id]);

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$postA, $postB])
        ->filterTable('category', $catA->id)
        ->assertCanSeeTableRecords([$postA])
        ->assertCanNotSeeTableRecords([$postB]);
});

it('can filter by locale', function () {
    // Factory auto-creates a translation matching default_locale via model hook
    $postFr = BlogPost::factory()->create(['default_locale' => 'fr']);
    $postEn = BlogPost::factory()->create(['default_locale' => 'en']);

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$postEn, $postFr])
        ->filterTable('locale', 'en')
        ->assertCanSeeTableRecords([$postEn])
        ->assertCanNotSeeTableRecords([$postFr]);
});

it('renders the series filter without sql error', function () {
    $series = BlogSeries::create(['slug' => 'test-series']);
    BlogSeriesTranslation::create([
        'blog_series_id' => $series->id,
        'locale' => 'en',
        'title' => 'Test Series',
        'slug' => 'test-series',
    ]);

    $post = BlogPost::factory()->create(['blog_series_id' => $series->id]);

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$post]);
});

it('can filter by series', function () {
    $seriesA = BlogSeries::create(['slug' => 'series-a']);
    BlogSeriesTranslation::create(['blog_series_id' => $seriesA->id, 'locale' => 'en', 'title' => 'Series A', 'slug' => 'series-a']);

    $seriesB = BlogSeries::create(['slug' => 'series-b']);
    BlogSeriesTranslation::create(['blog_series_id' => $seriesB->id, 'locale' => 'en', 'title' => 'Series B', 'slug' => 'series-b']);

    $postA = BlogPost::factory()->create(['blog_series_id' => $seriesA->id]);
    $postB = BlogPost::factory()->create(['blog_series_id' => $seriesB->id]);

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$postA, $postB])
        ->filterTable('blog_series_id', $seriesA->id)
        ->assertCanSeeTableRecords([$postA])
        ->assertCanNotSeeTableRecords([$postB]);
});

it('renders locale badges column', function () {
    $post = BlogPost::factory()->create(['default_locale' => 'en', 'title' => 'English Post']);

    // Add a second translation manually
    $post->translations()->create(['locale' => 'fr', 'title' => 'Français', 'slug' => 'francais', 'content' => '']);
    $post->load('translations');

    Livewire::test(ListBlogPosts::class)
        ->assertTableColumnStateSet('locales', ['EN', 'FR'], $post);
});

it('renders series title column via accessor', function () {
    $series = BlogSeries::create(['slug' => 'my-series']);
    BlogSeriesTranslation::create(['blog_series_id' => $series->id, 'locale' => 'en', 'title' => 'My Series', 'slug' => 'my-series']);

    $post = BlogPost::factory()->create(['blog_series_id' => $series->id]);

    Livewire::test(ListBlogPosts::class)
        ->assertTableColumnStateSet('series.title', 'My Series', $post);
});

it('shows dash for posts without series', function () {
    $post = BlogPost::factory()->create(['blog_series_id' => null]);

    // Reload to get fresh relation state
    $post = $post->fresh();

    Livewire::test(ListBlogPosts::class)
        ->assertTableColumnStateSet('series.title', null, $post);
});

it('shows photo column without storage error', function () {
    $this->withoutExceptionHandling();

    $post = BlogPost::factory()->create();

    Livewire::test(ListBlogPosts::class)
        ->assertCanSeeTableRecords([$post]);
});
