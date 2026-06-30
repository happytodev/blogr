<?php

use Filament\Actions\DeleteAction;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\CreateTag;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\EditTag;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\ListTags;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Tests\TestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(TestCase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render the tags list page', function () {
    $this->get(TagResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list tags', function () {
    $tags = Tag::factory()->count(3)->create();

    Livewire::test(ListTags::class)
        ->assertCanSeeTableRecords($tags);
});

it('can render the create tag page', function () {
    $this->get(TagResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create a tag', function () {
    Livewire::test(CreateTag::class)
        ->fillForm([
            'name' => 'New Tag',
            'slug' => 'new-tag',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('tags', [
        'name' => 'New Tag',
        'slug' => 'new-tag',
    ]);
});

it('can render the edit tag page', function () {
    $tag = Tag::factory()->create();

    $this->get(TagResource::getUrl('edit', ['record' => $tag]))
        ->assertSuccessful();
});

it('can update a tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
            'slug' => 'updated-name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $tag->refresh();
    expect($tag->name)->toBe('Updated Name');
    expect($tag->slug)->toBe('updated-name');
});

it('can delete a tag', function () {
    $tag = Tag::factory()->create();

    Livewire::test(EditTag::class, ['record' => $tag->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($tag);
});
