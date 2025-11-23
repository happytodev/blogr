<?php

use Illuminate\Support\Facades\App;

it('CMS page resource navigation label translated in English', function () {
    App::setLocale('en');
    $label = __('blogr::resources.cms_page.navigation_label');
    expect($label)->toBe('CMS Pages')->not->toContain('::');
});

it('CMS page resource navigation label translated in French', function () {
    App::setLocale('fr');
    $label = __('blogr::resources.cms_page.navigation_label');
    expect($label)->toBe('Pages CMS')->not->toContain('::');
});

it('CMS page resource navigation label translated in German', function () {
    App::setLocale('de');
    $label = __('blogr::resources.cms_page.navigation_label');
    expect($label)->toBe('CMS-Seiten')->not->toContain('::');
});

it('CMS page resource navigation label translated in Spanish', function () {
    App::setLocale('es');
    $label = __('blogr::resources.cms_page.navigation_label');
    expect($label)->toBe('Páginas CMS')->not->toContain('::');
});
