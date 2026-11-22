<?php

use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\CmsTestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/*
 * Regression test: #364 — the auto-save writes a placeholder post and a partial
 * translation (locale, title, slug) behind the form's back. Saving the post then
 * inserted that locale a second time and the (post, locale) unique index
 * answered 500. The form is the source of truth: after the save there must be
 * one translation for the locale, carrying what the form held.
 */

uses(CmsTestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $this->category = Category::factory()->create();
});

it('replaces the auto-saved translation instead of inserting a duplicate', function () {
    $component = Livewire::test(CreateBlogPost::class);

    // The user types into the translation item the repeater already opened.
    $itemKey = array_key_first($component->instance()->data['translations']);

    $component->set('data.category_id', $this->category->id)
        ->set('data.is_published', true)
        ->set("data.translations.{$itemKey}.locale", 'en')
        ->set("data.translations.{$itemKey}.title", 'AF-01 · day 11')
        ->set("data.translations.{$itemKey}.slug", 'af-01-day-11')
        ->set("data.translations.{$itemKey}.content", 'The body of the post.');

    // What the auto-save used to leave behind before 2.7.4: a placeholder and a
    // partial translation the repeater had never seen (slug unrelated, so the
    // slug rule does not block the form before it reaches the database).
    $placeholder = BlogPost::create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
    ]);
    $placeholder->translations()->create([
        'locale' => 'en',
        'title' => 'Untitled',
        'slug' => 'untitled-draft',
    ]);

    $component->set('record', $placeholder);

    $component->call('create');

    $post = $placeholder->fresh();

    expect($post->translations()->count())->toBe(1);
    expect($post->translations()->where('locale', 'en')->value('content'))->toBe('The body of the post.');
    expect($post->is_published)->toBeTrue();
});
