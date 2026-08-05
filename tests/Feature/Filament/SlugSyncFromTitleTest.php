<?php

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Happytodev\Blogr\Filament\Forms\SlugFieldTrait;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages\CreateBlogSeries;
use Happytodev\Blogr\Filament\Resources\Categories\Pages\EditCategory;
use Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages\EditCmsPageTranslation;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\EditTag;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);
    $this->session(['errors' => new ViewErrorBag]);
});

test('feature_blog_post_slug_reloads_from_title', function () {
    $categoryId = Category::factory()->create()->id;

    Livewire::test(CreateBlogPost::class)
        ->fillForm([
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => 'My Great Post',
                    'slug' => 'stale-slug',
                    'content' => 'Some content',
                ],
            ],
            'category_id' => $categoryId,
            'default_locale' => 'en',
            'is_published' => false,
        ])
        ->callFormComponentAction('translations.0.slug', 'syncSlugFromTitle')
        ->assertFormSet(['translations.0.slug' => 'my-great-post']);
});

test('feature_cms_page_translation_slug_reloads_from_title', function () {
    $page = CmsPage::factory()->create(['slug' => 'install', 'default_locale' => 'en']);
    $translation = CmsPageTranslation::create([
        'cms_page_id' => $page->id,
        'locale' => 'en',
        'title' => 'Installation Guide',
        'slug' => 'install',
    ]);

    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $page->id,
        'translation' => $translation->id,
    ])
        ->fillForm([
            'title' => 'Updated Guide',
            'slug' => 'stale-slug',
        ])
        ->callFormComponentAction(['slug'], 'syncSlugFromTitle')
        ->assertFormSet(['slug' => 'updated-guide']);
});

test('feature_blog_series_slug_reloads_from_title', function () {
    Livewire::test(CreateBlogSeries::class)
        ->fillForm([
            'slug' => 'series-base-slug',
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => 'My Awesome Series',
                    'slug' => 'stale-slug',
                ],
            ],
        ])
        ->callFormComponentAction('translations.0.slug', 'syncSlugFromTitle')
        ->assertFormSet(['translations.0.slug' => 'my-awesome-series']);
});

test('feature_category_slug_reloads_from_name', function () {
    $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'New Category Name'])
        ->callFormComponentAction(['slug'], 'syncSlugFromTitle')
        ->assertFormSet(['slug' => 'new-category-name']);
});

test('feature_tag_slug_reloads_from_name', function () {
    $tag = Tag::factory()->create(['name' => 'Old Tag', 'slug' => 'old-tag']);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm(['name' => 'New Tag Name'])
        ->callFormComponentAction(['slug'], 'syncSlugFromTitle')
        ->assertFormSet(['slug' => 'new-tag-name']);
});

test('feature_slug_reload_action_helper_returns_reload_action', function () {
    $action = SlugFieldTrait::syncSlugFromTitleAction();

    expect($action)->toBeInstanceOf(Action::class);
    expect($action->getName())->toBe('syncSlugFromTitle');
    expect($action->getIcon())->toBe('heroicon-m-arrow-path');
});

test('feature_blog_post_form_slug_fields_expose_reload_suffix_action', function () {
    $categoryId = Category::factory()->create()->id;

    Livewire::test(CreateBlogPost::class)
        ->fillForm([
            'translations' => [
                [
                    'locale' => 'en',
                    'title' => 'My Great Post',
                    'slug' => 'stale-slug',
                    'content' => 'Some content',
                ],
            ],
            'category_id' => $categoryId,
            'default_locale' => 'en',
            'is_published' => false,
        ])
        ->assertFormComponentActionExists('translations.0.slug', 'syncSlugFromTitle')
        ->assertFormComponentActionHasIcon('translations.0.slug', 'syncSlugFromTitle', 'heroicon-m-arrow-path');
});

test('feature_category_and_tag_form_slug_fields_expose_reload_suffix_action', function () {
    $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertFormComponentActionExists('slug', 'syncSlugFromTitle')
        ->assertFormComponentActionHasIcon('slug', 'syncSlugFromTitle', 'heroicon-m-arrow-path');

    $tag = Tag::factory()->create(['name' => 'Old', 'slug' => 'old']);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->assertFormComponentActionExists('slug', 'syncSlugFromTitle')
        ->assertFormComponentActionHasIcon('slug', 'syncSlugFromTitle', 'heroicon-m-arrow-path');
});

test('feature_blog_post_create_option_forms_expose_reload_suffix_action', function () {
    $component = Livewire::test(CreateBlogPost::class);
    $schema = $component->instance()->getSchema('form');

    $selects = collect($schema->getFlatComponents())
        ->filter(fn ($component) => $component instanceof Select);

    foreach (['category_id', 'tags'] as $selectName) {
        $select = $selects->firstWhere(fn ($component) => $component->getName() === $selectName);
        expect($select)->not->toBeNull();

        $optionSchema = $select->getCreateOptionActionForm($schema);
        $optionComponents = is_array($optionSchema) ? $optionSchema : collect($optionSchema?->getComponents() ?? []);

        $slugField = collect($optionComponents)->firstWhere(
            fn ($component) => $component instanceof TextInput && $component->getName() === 'slug',
        );
        expect($slugField)->not->toBeNull();

        $actions = $slugField->getSuffixActions();
        expect($actions)->not->toBeEmpty();
    }
});
