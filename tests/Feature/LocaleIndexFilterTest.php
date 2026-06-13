<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

uses(Happytodev\Blogr\Tests\LocalizedTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $category = Category::factory()->create();

    // Post with ONLY an English translation
    $this->enPost = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'is_published' => true,
        'published_at' => now(),
        'is_listed' => true,
        'title' => 'English Only',
        'slug' => 'english-only',
        'content' => 'English content',
    ]);
    // The factory auto-creates an 'en' translation via model hook

    // Post with ONLY a French translation
    $this->frPost = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'is_published' => true,
        'published_at' => now(),
        'is_listed' => true,
        'title' => 'Français seulement',
        'slug' => 'francais-seulement',
        'content' => 'Contenu français',
    ]);
    // Remove the auto-created English translation
    $this->frPost->translations()->where('locale', 'en')->delete();
    // Add a French translation
    BlogPostTranslation::create([
        'blog_post_id' => $this->frPost->id,
        'locale' => 'fr',
        'title' => 'Français seulement',
        'slug' => 'francais-seulement',
        'content' => 'Contenu français',
    ]);

    // Post with BOTH translations
    $this->bothPost = BlogPost::factory()->create([
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'default_locale' => 'en',
        'is_published' => true,
        'published_at' => now(),
        'is_listed' => true,
        'title' => 'Both Languages',
        'slug' => 'both-languages',
        'content' => 'English content',
    ]);
    BlogPostTranslation::create([
        'blog_post_id' => $this->bothPost->id,
        'locale' => 'fr',
        'title' => 'Les deux langues',
        'slug' => 'les-deux-langues',
        'content' => 'Contenu français',
    ]);
});

it('shows French-only post on French index', function () {
    $response = $this->get('/fr/blog');

    $response->assertStatus(200);
    $response->assertSee('Français seulement');
});

it('does not show French-only post on English index', function () {
    $response = $this->get('/en/blog');

    $response->assertStatus(200);
    $response->assertDontSee('Français seulement');
});

it('shows English-only post on English index', function () {
    $response = $this->get('/en/blog');

    $response->assertStatus(200);
    $response->assertSee('English Only');
});

it('does not show English-only post on French index', function () {
    $response = $this->get('/fr/blog');

    $response->assertStatus(200);
    $response->assertDontSee('English Only');
});

it('shows bilingual post on both indexes', function () {
    $enResponse = $this->get('/en/blog');
    $enResponse->assertSee('Both Languages');

    $frResponse = $this->get('/fr/blog');
    $frResponse->assertSee('Les deux langues');
});
