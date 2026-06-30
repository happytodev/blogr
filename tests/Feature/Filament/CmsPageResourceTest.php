<?php

use Filament\Actions\DeleteAction;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    // Create roles if they don't exist
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // Create admin user for Filament tests
    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);

    // Initialize ViewErrorBag in session to prevent Livewire validation errors
    // Livewire expects errors bag in session, but in test environment it's not always initialized
    $this->session(['errors' => new ViewErrorBag]);
});

test('it can render cms pages list page', function () {
    $this->get(CmsPageResource::getUrl('index'))
        ->assertSuccessful();
});

test('it can list cms pages', function () {
    $pages = CmsPage::factory()
        ->count(10)
        ->create();

    Livewire::test(CmsPageResource\Pages\ListCmsPages::class)
        ->assertCanSeeTableRecords($pages);
});

test('it can render create page', function () {
    $this->get(CmsPageResource::getUrl('create'))
        ->assertSuccessful();
});

test('it can create a cms page', function () {
    $pageData = [
        'slug' => 'test-page',
        'template' => CmsPageTemplate::LANDING->value,
        'is_published' => true,
        'published_at' => now()->format('Y-m-d H:i:s'),
        'is_homepage' => false,
    ];

    Livewire::test(CmsPageResource\Pages\CreateCmsPage::class)
        ->fillForm($pageData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('cms_pages', [
        'slug' => 'test-page',
        'template' => CmsPageTemplate::LANDING->value,
        'is_published' => true,
    ]);
});

test('it can create a page with blocks', function () {
    $pageData = [
        'slug' => 'page-with-blocks',
        'template' => CmsPageTemplate::LANDING->value,
        'is_published' => true,
        'published_at' => now()->format('Y-m-d\TH:i:s'),
        'default_locale' => 'en',
    ];

    Livewire::test(CmsPageResource\Pages\CreateCmsPage::class)
        ->fillForm($pageData)
        ->call('create')
        ->assertHasNoFormErrors();

    $page = CmsPage::where('slug', 'page-with-blocks')->first();
    expect($page->slug)->toBe('page-with-blocks');

    $translation = $page->translations()->first();
    expect($translation)->not->toBeNull();
    expect($translation->blocks)->toBeArray();
});

test('it validates required fields on create', function () {
    Livewire::test(CmsPageResource\Pages\CreateCmsPage::class)
        ->fillForm([
            'slug' => '', // Required field empty
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'required']);
});

test('it validates unique slug on create', function () {
    CmsPage::factory()->create(['slug' => 'existing-slug']);

    Livewire::test(CmsPageResource\Pages\CreateCmsPage::class)
        ->fillForm([
            'slug' => 'existing-slug',
            'template' => CmsPageTemplate::LANDING->value,
            'is_published' => true,
            'published_at' => now()->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('it validates reserved slugs on create', function () {
    Livewire::test(CmsPageResource\Pages\CreateCmsPage::class)
        ->fillForm([
            'slug' => 'admin', // Reserved slug
            'template' => CmsPageTemplate::LANDING->value,
            'is_published' => true,
            'published_at' => now()->format('Y-m-d H:i:s'),
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

test('it can render edit page', function () {
    $page = CmsPage::factory()->create();

    $this->get(CmsPageResource::getUrl('edit', ['record' => $page]))
        ->assertSuccessful();
});

test('it can retrieve data for edit', function () {
    $page = CmsPage::factory()->create();

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page->getRouteKey()])
        ->assertFormSet([
            'slug' => $page->slug,
            'template' => $page->template,
            'is_published' => $page->is_published,
            'is_homepage' => $page->is_homepage,
        ]);
});

test('it can update a cms page', function () {
    $page = CmsPage::factory()->create(['published_at' => now()]);

    $newData = [
        'slug' => 'updated-slug',
        'template' => CmsPageTemplate::CUSTOM->value,
        'is_published' => false,
        'published_at' => now()->format('Y-m-d\TH:i:s'),
    ];

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page->getRouteKey()])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh())
        ->slug->toBe('updated-slug')
        ->template->value->toBe(CmsPageTemplate::CUSTOM->value)
        ->is_published->toBeFalse();
});

test('it can update page slug via edit form', function () {
    $page = CmsPage::factory()->create(['published_at' => now()]);

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'slug' => 'new-slug-value',
            'published_at' => now()->format('Y-m-d\TH:i:s'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh()->slug)->toBe('new-slug-value');
});

test('it validates unique slug on edit', function () {
    $page1 = CmsPage::factory()->create(['slug' => 'page-1']);
    $page2 = CmsPage::factory()->create(['slug' => 'page-2']);

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page2->getRouteKey()])
        ->fillForm(['slug' => 'page-1']) // Try to use existing slug
        ->call('save')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('it can delete a cms page', function () {
    $page = CmsPage::factory()->create();

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($page);
});

test('it can toggle published status', function () {
    $page = CmsPage::factory()->create(['is_published' => false, 'published_at' => now()]);

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'is_published' => true,
            'published_at' => now()->format('Y-m-d\TH:i:s'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh()->is_published)->toBeTrue();
});

test('it can set homepage', function () {
    $page = CmsPage::factory()->create(['is_homepage' => false, 'published_at' => now()]);

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'is_homepage' => true,
            'published_at' => now()->format('Y-m-d\TH:i:s'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh()->is_homepage)->toBeTrue();
});

test('it sets only one page as homepage when multiple are marked', function () {
    $page1 = CmsPage::factory()->create(['is_homepage' => true, 'published_at' => now()]);
    $page2 = CmsPage::factory()->create(['is_homepage' => false, 'published_at' => now()]);

    Livewire::test(CmsPageResource\Pages\EditCmsPage::class, ['record' => $page2->getRouteKey()])
        ->fillForm([
            'is_homepage' => true,
            'published_at' => now()->format('Y-m-d\TH:i:s'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page2->refresh()->is_homepage)->toBeTrue();
    expect($page1->refresh()->is_homepage)->toBeFalse();
});

test('it can schedule publishing', function () {
    $futureDate = now()->addWeek();

    $pageData = [
        'slug' => 'scheduled-page',
        'template' => CmsPageTemplate::LANDING->value,
        'is_published' => false,
        'published_at' => $futureDate->format('Y-m-d H:i:s'),
    ];

    Livewire::test(CmsPageResource\Pages\CreateCmsPage::class)
        ->fillForm($pageData)
        ->call('create')
        ->assertHasNoFormErrors();

    $page = CmsPage::where('slug', 'scheduled-page')->first();
    expect($page->published_at->timestamp)->toBe($futureDate->timestamp);
});

test('it can search pages by slug', function () {
    $page1 = CmsPage::factory()->create(['slug' => 'unique-search-term']);
    $page2 = CmsPage::factory()->create(['slug' => 'another-page']);

    Livewire::test(CmsPageResource\Pages\ListCmsPages::class)
        ->searchTable('unique-search-term')
        ->assertCanSeeTableRecords([$page1])
        ->assertCanNotSeeTableRecords([$page2]);
});

test('it can filter pages by template', function () {
    $landingPage = CmsPage::factory()->create(['template' => CmsPageTemplate::LANDING]);
    $customPage = CmsPage::factory()->create(['template' => CmsPageTemplate::CUSTOM]);

    Livewire::test(CmsPageResource\Pages\ListCmsPages::class)
        ->filterTable('template', CmsPageTemplate::LANDING->value)
        ->assertCanSeeTableRecords([$landingPage])
        ->assertCanNotSeeTableRecords([$customPage]);
});

test('it can filter pages by published status', function () {
    $publishedPage = CmsPage::factory()->create(['is_published' => true]);
    $draftPage = CmsPage::factory()->create(['is_published' => false]);

    Livewire::test(CmsPageResource\Pages\ListCmsPages::class)
        ->filterTable('is_published', true)
        ->assertCanSeeTableRecords([$publishedPage])
        ->assertCanNotSeeTableRecords([$draftPage]);
});

test('it can bulk delete pages', function () {
    $pages = CmsPage::factory()->count(3)->create();

    Livewire::test(CmsPageResource\Pages\ListCmsPages::class)
        ->callTableBulkAction('delete', $pages);

    foreach ($pages as $page) {
        $this->assertModelMissing($page);
    }
});
