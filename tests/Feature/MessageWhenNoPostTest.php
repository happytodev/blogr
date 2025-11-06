<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Mockery\MockInterface;
use Illuminate\Foundation\Vite;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\HtmlString;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('Display "No posts yet" if there are no posts', function () {
    // la base est rafraîchie par RefreshDatabase, donc pas de posts par défaut
    // Remplace route('blog.index') par '/blog' ou le nom de route correct si nécessaire
    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSeeText('No posts yet');
});