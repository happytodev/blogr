<?php

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $this->session(['errors' => new ViewErrorBag]);
});

function getEditBlogPostPreviewAction(int $postId): Action
{
    $component = Livewire::test(EditBlogPost::class, ['record' => $postId]);
    $component->assertStatus(200);

    $method = new ReflectionMethod(EditBlogPost::class, 'getFormActions');
    $method->setAccessible(true);
    $actions = collect($method->invoke($component->instance()));

    return $actions->first(fn ($action) => $action->getName() === 'preview');
}

test('preview action builds a signed url for single translation posts', function () {
    $category = Category::factory()->create();
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'title' => 'Preview Action Post',
        'slug' => 'preview-action-post',
        'content' => 'Content',
    ]);

    $translation = $post->translations()->first();

    $preview = getEditBlogPostPreviewAction($post->id);

    expect($preview)->not->toBeNull();
    expect($preview->getSchema(new Schema))->toBeNull();
    expect($preview->getUrl())
        ->toContain('_preview/post/'.$translation->id)
        ->toContain('signature=');
});

test('preview action offers a language select for multi translation posts', function () {
    $category = Category::factory()->create();
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'title' => 'Multi Locale Post',
        'slug' => 'multi-locale-post',
        'content' => 'Content',
    ]);

    $post->translations()->create([
        'locale' => 'fr',
        'title' => 'Article Multilingue',
        'slug' => 'article-multilingue',
        'content' => 'Contenu',
    ]);

    $preview = getEditBlogPostPreviewAction($post->id);

    expect($preview)->not->toBeNull();

    $schema = $preview->getSchema(new Schema);
    expect($schema)->not->toBeNull();

    $components = $schema->getComponents();
    expect($components)->toHaveCount(1);
    expect($components[0]->getName())->toBe('translation_id');
});

test('preview action is hidden when the post has no translations', function () {
    $category = Category::factory()->create();
    $post = BlogPost::factory()->create([
        'user_id' => auth()->id(),
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'title' => 'No Translation Post',
        'slug' => 'no-translation-post',
        'content' => 'Content',
    ]);

    $post->translations()->delete();

    $preview = getEditBlogPostPreviewAction($post->id);

    expect($preview)->not->toBeNull();
    expect($preview->isVisible())->toBeFalse();
});
