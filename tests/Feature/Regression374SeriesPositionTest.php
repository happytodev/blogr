<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;

/*
 * Regression tests: #374 — a post lost its place in its series as soon as it was
 * edited. The form showed an existing integer position as "Custom position...",
 * the custom field stayed empty, saving wrote null, and the model sent the post to
 * the end of the series — every edit, forever (positions read 1, 4, 4 in the wild).
 *
 * Two things are tested apart: what the form decides (resolveSeriesPosition, no
 * Filament page needed) and what the model does with a null position on its own.
 */

uses(CmsTestCase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->category = Category::factory()->create();

    $this->series = BlogSeries::create(['slug' => 'amber-fossils', 'is_published' => true]);
    $this->series->translations()->create([
        'locale' => 'en',
        'title' => 'Amber Fossils',
        'slug' => 'amber-fossils',
        'description' => 'A series.',
    ]);

    $this->other = BlogSeries::create(['slug' => 'other', 'is_published' => true]);
    $this->other->translations()->create([
        'locale' => 'en',
        'title' => 'Other',
        'slug' => 'other',
        'description' => 'Another series.',
    ]);

    $this->makePost = function (int $position, string $title, ?BlogSeries $series = null): BlogPost {
        $series ??= $this->series;

        $post = BlogPost::create([
            'user_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'blog_series_id' => $series->id,
            'series_position' => $position,
            'default_locale' => 'en',
            'is_published' => false,
        ]);

        $post->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'slug' => str($title)->slug()->value(),
            'content' => 'Some body.',
        ]);

        return $post->fresh();
    };

    $this->first = ($this->makePost)(1, 'Day eleven');
    $this->second = ($this->makePost)(2, 'Day thirty three');
    $this->third = ($this->makePost)(3, 'Day one eighty eight');
});

it('keeps the position of a post saved without touching the field', function () {
    // What the form sends when the editor changes nothing: the select still says
    // "custom" and the number field is empty.
    $position = BlogPost::resolveSeriesPosition(
        $this->series->id,
        'custom',
        null,
        $this->second->series_position,
        $this->second->blog_series_id,
        $this->second->id,
    );

    expect($position)->toBe(2);
});

it('keeps the number an editor types', function () {
    expect(BlogPost::resolveSeriesPosition($this->series->id, 'custom', 7, 2, $this->series->id, $this->second->id))
        ->toBe(7);
});

it('moves a post to the end when the editor asks for it', function () {
    expect(BlogPost::resolveSeriesPosition($this->series->id, 'auto-bottom', null, 1, $this->series->id, $this->first->id))
        ->toBe(4);
});

it('moves a post to the top when the editor asks for it', function () {
    expect(BlogPost::resolveSeriesPosition($this->series->id, 'auto-top', null, 3, $this->series->id, $this->third->id))
        ->toBe(1);
});

it('gives a post that changes series the end of the new one', function () {
    $position = BlogPost::resolveSeriesPosition(
        $this->other->id,
        'custom',
        null,
        $this->third->series_position,   // 3, meaningless in an empty series
        $this->third->blog_series_id,
        $this->third->id,
    );

    expect($position)->toBe(1);
});

it('clears the position of a post removed from its series', function () {
    expect(BlogPost::resolveSeriesPosition(null, 'custom', 3, 3, $this->series->id, $this->third->id))
        ->toBeNull();
});

it('appends a post to the end of a series only when it joins one', function () {
    $newcomer = BlogPost::create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'default_locale' => 'en',
        'is_published' => false,
    ]);

    expect($newcomer->series_position)->toBe(4);
});

it('never sends a post to the end of its series just because it was saved again', function () {
    // A lost position must not be silently "repaired" into last place: that is
    // what turned one empty field into 1, 4, 4.
    $this->second->forceFill(['series_position' => null])->save();

    expect($this->second->fresh()->series_position)->toBeNull();
});

it('gives a post moved to another series a place there', function () {
    $this->third->update(['blog_series_id' => $this->other->id, 'series_position' => null]);

    expect($this->third->fresh()->series_position)->toBe(1);
});

it('leaves no two posts at the same position after repeated saves', function () {
    for ($i = 0; $i < 3; $i++) {
        $this->third->update(['is_published' => false]);
    }

    $positions = BlogPost::where('blog_series_id', $this->series->id)
        ->orderBy('series_position')
        ->pluck('series_position')
        ->all();

    expect($positions)->toBe([1, 2, 3]);
});
