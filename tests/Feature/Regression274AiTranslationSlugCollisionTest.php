<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\LocaleService;
use Happytodev\Blogr\Services\Translation\TranslationProviderFactory;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    app(LocaleService::class)->flushCache();

    config()->set('blogr.translation', [
        'provider' => 'libretranslate',
        'libretranslate' => ['url' => 'http://localhost:5000'],
    ]);

    config()->set('blogr.locales.available', ['en', 'fr']);
    config()->set('blogr.locales.default', 'en');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $category = Category::factory()->create();

    // Post A — will already have a French translation whose slug will collide
    $this->postA = BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'title' => 'First Post',
        'slug' => 'first-post',
        'content' => 'Content of first post',
        'tldr' => 'Summary A',
        'meta_title' => 'First Post SEO',
        'meta_description' => 'SEO A',
        'meta_keywords' => 'first, post',
    ]);

    // Pre-create a French translation for Post A with a known slug
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $this->postA->id,
        'locale' => 'fr',
        'title' => 'Premier article',
        'slug' => 'slug-existant',
        'content' => 'Contenu du premier article',
        'tldr' => 'Résumé A',
        'seo_title' => 'Premier article SEO',
        'seo_description' => 'SEO A',
        'seo_keywords' => 'premier, article',
    ]);

    // Post B — will be translated, and the AI will generate the colliding slug
    $this->postB = BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'title' => 'Second Post',
        'slug' => 'second-post',
        'content' => 'Content of second post',
        'tldr' => 'Summary B',
        'meta_title' => 'Second Post SEO',
        'meta_description' => 'SEO B',
        'meta_keywords' => 'second, post',
    ]);
});

it('deduplicates slug when AI translation produces an already existing slug', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Deuxième article'])
            ->push(['translatedText' => 'Résumé B'])
            ->push(['translatedText' => 'Contenu du deuxième article'])
            ->push(['translatedText' => 'Deuxième article SEO'])
            ->push(['translatedText' => 'SEO B'])
            ->push(['translatedText' => 'deuxième, article'])
            ->push(['translatedText' => 'slug existant']),
    ]);

    $this->actingAs($this->admin);

    $component = Livewire::test(EditBlogPost::class, [
        'record' => $this->postB->id,
    ]);

    $reflection = new ReflectionClass($component->instance());
    $method = $reflection->getMethod('translateWithAI');
    $method->setAccessible(true);
    $provider = app(TranslationProviderFactory::class)->make();
    $method->invoke($component->instance(), $provider, 'en', 'fr');

    $frTranslation = BlogPostTranslation::where('blog_post_id', $this->postB->id)
        ->where('locale', 'fr')
        ->first();

    expect($frTranslation)->not->toBeNull();
    expect($frTranslation->slug)->toBe('slug-existant-1');

    $postAFr = BlogPostTranslation::where('blog_post_id', $this->postA->id)
        ->where('locale', 'fr')
        ->first();

    expect($postAFr->slug)->toBe('slug-existant');
});

it('deduplicates slug on update when the existing translation slug is changed to a colliding one', function () {
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $this->postB->id,
        'locale' => 'fr',
        'title' => 'Deuxième article',
        'slug' => 'slug-non-conflictuel',
        'content' => 'Contenu du deuxième article',
        'tldr' => 'Résumé B',
        'seo_title' => 'Deuxième article SEO',
        'seo_description' => 'SEO B',
        'seo_keywords' => 'deuxième, article',
    ]);

    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Deuxième article (mis à jour)'])
            ->push(['translatedText' => 'Résumé B (mis à jour)'])
            ->push(['translatedText' => 'Contenu mis à jour'])
            ->push(['translatedText' => 'Deuxième article SEO mis à jour'])
            ->push(['translatedText' => 'SEO B mis à jour'])
            ->push(['translatedText' => 'deuxième, article, mis à jour'])
            ->push(['translatedText' => 'slug existant']),
    ]);

    $this->actingAs($this->admin);

    $component = Livewire::test(EditBlogPost::class, [
        'record' => $this->postB->id,
    ]);

    $reflection = new ReflectionClass($component->instance());
    $method = $reflection->getMethod('translateWithAI');
    $method->setAccessible(true);
    $provider = app(TranslationProviderFactory::class)->make();
    $method->invoke($component->instance(), $provider, 'en', 'fr');

    $frTranslation = BlogPostTranslation::where('blog_post_id', $this->postB->id)
        ->where('locale', 'fr')
        ->first();

    expect($frTranslation)->not->toBeNull();
    expect($frTranslation->slug)->toBe('slug-existant-1');

    $postAFr = BlogPostTranslation::where('blog_post_id', $this->postA->id)
        ->where('locale', 'fr')
        ->first();

    expect($postAFr->slug)->toBe('slug-existant');
});

it('deduplicates slug with incrementing suffix when multiple collisions exist', function () {
    // Post C — has a French translation with slug 'slug-existant-1' (blocks 'slug-existant' → 'slug-existant-1' → 'slug-existant-2')
    $postC = BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->postA->category_id,
        'default_locale' => 'en',
        'title' => 'Third Post',
        'slug' => 'third-post',
        'content' => 'Content of third post',
        'tldr' => 'Summary C',
        'meta_title' => 'Third Post SEO',
        'meta_description' => 'SEO C',
        'meta_keywords' => 'third, post',
    ]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $postC->id,
        'locale' => 'fr',
        'title' => 'Troisième article',
        'slug' => 'slug-existant-1',
        'content' => 'Contenu du troisième article',
        'tldr' => 'Résumé C',
        'seo_title' => 'Troisième article SEO',
        'seo_description' => 'SEO C',
        'seo_keywords' => 'troisième, article',
    ]);

    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Deuxième article'])
            ->push(['translatedText' => 'Résumé B'])
            ->push(['translatedText' => 'Contenu du deuxième article'])
            ->push(['translatedText' => 'Deuxième article SEO'])
            ->push(['translatedText' => 'SEO B'])
            ->push(['translatedText' => 'deuxième, article'])
            ->push(['translatedText' => 'slug existant']),
    ]);

    $this->actingAs($this->admin);

    $component = Livewire::test(EditBlogPost::class, [
        'record' => $this->postB->id,
    ]);

    $reflection = new ReflectionClass($component->instance());
    $method = $reflection->getMethod('translateWithAI');
    $method->setAccessible(true);
    $provider = app(TranslationProviderFactory::class)->make();
    $method->invoke($component->instance(), $provider, 'en', 'fr');

    $frTranslation = BlogPostTranslation::where('blog_post_id', $this->postB->id)
        ->where('locale', 'fr')
        ->first();

    expect($frTranslation)->not->toBeNull();
    expect($frTranslation->slug)->toBe('slug-existant-2');
});
