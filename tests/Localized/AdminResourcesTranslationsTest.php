<?php

use Illuminate\Support\Facades\App;

describe('Blog Post Resource Translations', function () {
    it('Blog post resource navigation label translated in English', function () {
        App::setLocale('en');
        $label = __('blogr::resources.blog_post.navigation_label');
        expect($label)->toBe('Blog Posts')->not->toContain('::');
    });

    it('Blog post resource navigation label translated in French', function () {
        App::setLocale('fr');
        $label = __('blogr::resources.blog_post.navigation_label');
        expect($label)->toBe('Articles')->not->toContain('::');
    });

    it('Blog post resource navigation label translated in German', function () {
        App::setLocale('de');
        $label = __('blogr::resources.blog_post.navigation_label');
        expect($label)->toBe('Blog-Beiträge')->not->toContain('::');
    });

    it('Blog post resource navigation label translated in Spanish', function () {
        App::setLocale('es');
        $label = __('blogr::resources.blog_post.navigation_label');
        expect($label)->toBe('Publicaciones de Blog')->not->toContain('::');
    });
});

describe('Blog Series Resource Translations', function () {
    it('Blog series resource navigation label translated in English', function () {
        App::setLocale('en');
        $label = __('blogr::resources.blog_series.navigation_label');
        expect($label)->toBe('Blog Series')->not->toContain('::');
    });

    it('Blog series resource navigation label translated in French', function () {
        App::setLocale('fr');
        $label = __('blogr::resources.blog_series.navigation_label');
        expect($label)->toBe('Séries de Blogs')->not->toContain('::');
    });

    it('Blog series resource navigation label translated in German', function () {
        App::setLocale('de');
        $label = __('blogr::resources.blog_series.navigation_label');
        expect($label)->toBe('Blog-Serien')->not->toContain('::');
    });

    it('Blog series resource navigation label translated in Spanish', function () {
        App::setLocale('es');
        $label = __('blogr::resources.blog_series.navigation_label');
        expect($label)->toBe('Series de Blog')->not->toContain('::');
    });
});
