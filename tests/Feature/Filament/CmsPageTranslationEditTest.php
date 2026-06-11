<?php

use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages\EditCmsPageTranslation;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(CmsTestCase::class);

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);
    $this->session(['errors' => new ViewErrorBag]);

    $this->page = CmsPage::factory()->create([
        'slug' => 'install',
        'default_locale' => 'en',
    ]);

    $this->enTranslation = CmsPageTranslation::create([
        'cms_page_id' => $this->page->id,
        'locale' => 'en',
        'title' => 'Installation Guide',
        'slug' => 'install',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Welcome',
                    'subtitle' => 'Get started here',
                    'cta_link_type' => 'blog',
                    'cta_url' => null,
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                ],
            ],
        ],
    ]);

    $this->frTranslation = CmsPageTranslation::create([
        'cms_page_id' => $this->page->id,
        'locale' => 'fr',
        'title' => "Guide d'Installation",
        'slug' => 'installation',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Bienvenue',
                    'subtitle' => 'Commencez ici',
                    'cta_link_type' => 'blog',
                    'cta_url' => null,
                    'cta_category_id' => null,
                    'cta_cms_page_id' => null,
                    'alignment' => 'center',
                ],
            ],
        ],
    ]);
});

test('it can render the translation edit page', function () {
    $this->get(CmsPageResource::getUrl('edit-translation', [
        'record' => $this->page,
        'translation' => $this->enTranslation,
    ]))->assertSuccessful();
});

test('it displays the translation form', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->enTranslation->id,
    ])
        ->assertFormSet([
            'title' => 'Installation Guide',
            'slug' => 'install',
        ]);
});

test('it can update translation title and slug', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->enTranslation->id,
    ])
        ->fillForm([
            'title' => 'Updated Guide',
            'slug' => 'install-v2',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->enTranslation->refresh();
    expect($this->enTranslation->title)->toBe('Updated Guide');
    expect($this->enTranslation->slug)->toBe('install-v2');
});

test('it does not affect other translations when saving one', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->enTranslation->id,
    ])
        ->fillForm([
            'title' => 'EN Updated Title',
            'slug' => 'install-v2',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->frTranslation->refresh();
    expect($this->frTranslation->title)->toBe("Guide d'Installation");
    expect($this->frTranslation->slug)->toBe('installation');
});

test('it validates required fields on translation', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->enTranslation->id,
    ])
        ->fillForm([
            'title' => '',
            'slug' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required', 'slug' => 'required']);
});

test('the main edit page shows translation grid with links', function () {
    $this->get(CmsPageResource::getUrl('edit', ['record' => $this->page]))
        ->assertSuccessful()
        ->assertSee('Traductions')
        ->assertSee('EN')
        ->assertSee('FR');
});

test('the main edit page header does not have per-translation buttons', function () {
    $this->get(CmsPageResource::getUrl('edit', ['record' => $this->page]))
        ->assertSuccessful()
        ->assertDontSee('edit_');
});

test('it stays on the current page after save (no redirect)', function () {
    Livewire::test(EditCmsPageTranslation::class, [
        'record' => $this->page->id,
        'translation' => $this->enTranslation->id,
    ])
        ->fillForm([
            'title' => 'Updated',
            'slug' => 'install',
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('record.title', 'Updated');
});
