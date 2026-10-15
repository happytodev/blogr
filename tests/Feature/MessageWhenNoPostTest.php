<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('Display "No posts yet" if there are no posts', function () {
    // The database is refreshed by RefreshDatabase, so there are no posts by default
    // Replace route('blog.index') with '/blog' or the correct route name if necessary
    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSeeText('No posts yet');
});
