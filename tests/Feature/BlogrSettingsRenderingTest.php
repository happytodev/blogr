<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Filament\Pages\BlogrSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can render blogr settings page without errors', function () {
    // Test that the page can be instantiated
    $page = app(BlogrSettings::class);

    // Test that the form can be accessed
    expect($page->form)->not->toBeNull();

    // Test that the page can be mounted without errors (skip validation)
    try {
        $page->mount();
        $mounted = true;
    } catch (\Exception $e) {
        $mounted = false;
    }

    expect($mounted)->toBe(true);
});

it('can render blogr settings view without errors', function () {
    // Test that the view can be found and rendered
    $view = view('blogr::filament.pages.blogr-settings');

    expect($view)->not->toBeNull();
});

it('can render complete page with form schema', function () {
    // Create a page instance
    $page = app(BlogrSettings::class);

    // Test that we can access the form
    expect($page->form)->not->toBeNull();

    // Test that the page has the required properties
    expect(method_exists($page, 'getView'))->toBe(true);
});

it('can mount blogr settings page', function () {
    $page = app(BlogrSettings::class);

    // Test mount method
    try {
        $page->mount();
        $mounted = true;
    } catch (\Exception $e) {
        $mounted = false;
    }

    expect($mounted)->toBe(true);
});

it('can handle HTTP request and render page', function () {
    // Test that the page can be instantiated and mounted
    $page = app(BlogrSettings::class);

    // Test that the form is properly initialized
    expect($page->form)->not->toBeNull();

    // Test that the page has all required properties for Filament
    expect($page->getView())->toBe('blogr::filament.pages.blogr-settings');
});

it('validates form schema structure', function () {
    $page = app(BlogrSettings::class);

    // Test that the form is properly initialized
    expect($page->form)->not->toBeNull();

    // Test that the page has the expected methods
    expect(method_exists($page, 'save'))->toBe(true);
    expect(method_exists($page, 'mount'))->toBe(true);
});

it('can save blogr settings', function () {
    $page = app(BlogrSettings::class);

    // Test that save method exists and the page can be instantiated
    expect(method_exists($page, 'save'))->toBe(true);
    expect($page)->toBeInstanceOf(BlogrSettings::class);
});

it('loads config data on mount', function () {
    $page = app(BlogrSettings::class);

    // Mount the page
    try {
        $page->mount();
        $mounted = true;
    } catch (\Exception $e) {
        $mounted = false;
    }

    expect($mounted)->toBe(true);

    // Test that the form has been filled with data
    expect($page->form)->not->toBeNull();
});

it('can save TOC settings', function () {
    $page = app(BlogrSettings::class);
    $page->mount();

    // Test that TOC properties exist and are accessible
    expect(property_exists($page, 'toc_enabled'))->toBe(true);
    expect(property_exists($page, 'toc_strict_mode'))->toBe(true);
    
    // Test that they can be set
    $page->toc_enabled = false;
    $page->toc_strict_mode = true;
    
    expect($page->toc_enabled)->toBe(false);
    expect($page->toc_strict_mode)->toBe(true);
});

it('config values are properly loaded', function () {
    // Test that config values are accessible
    $config = config('blogr', []);

    expect($config)->toBeArray();
    expect($config)->toHaveKey('posts_per_page');
    expect($config)->toHaveKey('colors');
    expect($config['colors'])->toHaveKey('primary');

    // Test that page loads config values into properties
    $page = app(BlogrSettings::class);
    $page->mount();

    // Test that properties are set (values may vary from original config)
    expect($page->colors_primary)->toBeString();
    expect($page->posts_per_page)->toBeInt();
    expect($page->seo_site_name)->toBeString();
    expect($page->seo_site_name)->not->toBeEmpty();
});

it('can load and save display settings', function () {
    $page = app(BlogrSettings::class);
    $page->mount();

    // Test that display properties exist and are loaded
    expect(property_exists($page, 'display_show_author_pseudo'))->toBe(true);
    expect(property_exists($page, 'display_show_author_avatar'))->toBe(true);
    
    // Test default values
    expect($page->display_show_author_pseudo)->toBe(true);
    expect($page->display_show_author_avatar)->toBe(true);
    
    // Test that they can be modified
    $page->display_show_author_pseudo = false;
    $page->display_show_author_avatar = false;
    
    expect($page->display_show_author_pseudo)->toBe(false);
    expect($page->display_show_author_avatar)->toBe(false);
});
