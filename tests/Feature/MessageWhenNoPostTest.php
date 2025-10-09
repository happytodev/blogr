<?php

use Mockery\MockInterface;
use Illuminate\Foundation\Vite;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\HtmlString;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app['config']->set('blogr.locales.enabled', false);
    $this->app['config']->set('blogr.route.prefix', 'blog');
    $this->app['config']->set('blogr.route.homepage', false);
    $this->app->register(\Happytodev\Blogr\BlogrServiceProvider::class, true);
});

it('Display "No posts yet" if there are no posts', function () {
    // la base est rafraîchie par RefreshDatabase, donc pas de posts par défaut
    // Remplace route('blog.index') par '/blog' ou le nom de route correct si nécessaire
    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSeeText('No posts yet');
});