<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('blogr.ui.back_to_top.enabled', true);
});

it('renders back to top button on frontend pages', function () {
    $response = $this->get(route('blog.index'));
    $response->assertStatus(200);

    $response->assertSee('</body>', false);

    $response->assertSee('blogr-back-to-top', false);
});
