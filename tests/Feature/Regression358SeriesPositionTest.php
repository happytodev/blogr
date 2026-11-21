<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

it('removes a series position that is not a number, so the integer column accepts it', function () {
    // The select keeps 'auto-bottom' as its default even when the series field is
    // hidden; MySQL refuses that word in an integer column.
    $page = (new ReflectionClass(CreateBlogPost::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(CreateBlogPost::class, 'mutateFormDataBeforeCreate');
    $method->setAccessible(true);

    $data = $method->invoke($page, [
        'title' => 'No series here',
        'series_position' => 'auto-bottom',
    ]);

    expect($data['series_position'])->toBeNull();
});

it('keeps a numeric position untouched', function () {
    $page = (new ReflectionClass(EditBlogPost::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(EditBlogPost::class, 'mutateFormDataBeforeSave');
    $method->setAccessible(true);

    $data = $method->invoke($page, [
        'blog_series_id' => 1,
        'series_position' => 4,
    ]);

    expect($data['series_position'])->toBe(4);
});
