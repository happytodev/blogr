<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource;
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

    // Enable translation via config
    config()->set('blogr.translation', [
        'provider' => 'libretranslate',
        'libretranslate' => ['url' => 'http://localhost:5000'],
    ]);

    // Set up locales
    config()->set('blogr.locales.available', ['en', 'fr']);
    config()->set('blogr.locales.default', 'en');

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    // Create a category
    $category = Category::factory()->create();

    // Create a blog post with English translation (factory creates translation via model hook)
    $this->post = BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'content' => 'This is the content',
        'tldr' => 'A short summary',
        'meta_title' => 'Hello World SEO',
        'meta_description' => 'SEO description',
        'meta_keywords' => 'hello, world',
    ]);
});

it('shows translate with AI button on edit page when provider is configured', function () {
    Http::fake([
        '*/languages' => Http::response([]),
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(BlogPostResource::getUrl('edit', ['record' => $this->post]));

    $response->assertStatus(200);
    $response->assertSee('Translate with AI');
});

it('translates blog post fields via the translate action', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Bonjour le monde'])
            ->push(['translatedText' => 'Un court résumé'])
            ->push(['translatedText' => 'Voici le contenu'])
            ->push(['translatedText' => 'Bonjour le monde SEO'])
            ->push(['translatedText' => 'Description SEO'])
            ->push(['translatedText' => 'bonjour, monde'])
            ->push(['translatedText' => 'bonjour-le-monde']),
    ]);

    $this->actingAs($this->admin);

    $component = Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $reflection = new ReflectionClass($component->instance());
    $method = $reflection->getMethod('translateWithAI');
    $method->setAccessible(true);
    $provider = app(TranslationProviderFactory::class)->make();
    $method->invoke($component->instance(), $provider, 'en', 'fr');

    $frTranslation = BlogPostTranslation::where('blog_post_id', $this->post->id)
        ->where('locale', 'fr')
        ->first();

    expect($frTranslation)->not->toBeNull()
        ->and($frTranslation->title)->toBe('Bonjour le monde');
});

it('creates new translation row when target locale does not exist yet', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Bonjour le monde'])
            ->push(['translatedText' => 'Un court résumé'])
            ->push(['translatedText' => 'Voici le contenu'])
            ->push(['translatedText' => 'Bonjour le monde SEO'])
            ->push(['translatedText' => 'Description SEO'])
            ->push(['translatedText' => 'bonjour, monde'])
            ->push(['translatedText' => 'bonjour-le-monde']),
    ]);

    $this->actingAs($this->admin);

    expect(BlogPostTranslation::where('blog_post_id', $this->post->id)->count())->toBe(1);

    $component = Livewire::test(EditBlogPost::class, [
        'record' => $this->post->id,
    ]);

    $reflection = new ReflectionClass($component->instance());
    $method = $reflection->getMethod('translateWithAI');
    $method->setAccessible(true);
    $provider = app(TranslationProviderFactory::class)->make();
    $method->invoke($component->instance(), $provider, 'en', 'fr');

    expect(BlogPostTranslation::where('blog_post_id', $this->post->id)->count())->toBe(2);
});
