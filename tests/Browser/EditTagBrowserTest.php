<?php

use Workbench\App\Models\User;
use Happytodev\Blogr\Models\Tag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\{actingAs};

uses(RefreshDatabase::class);

it('can login and access admin dashboard', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    // For testing purposes, bypass authentication and directly visit the tags page
    $page = visit('/admin/tags')
         ->assertPathIs('/admin/tags')
         ->assertSee('Tags');
    $page->screenshot();
})->skip('Browser tests require proper Dusk/browser testing setup with authentication');

it('can create and edit tag through browser workflow', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    // Directly visit the tags page (authentication bypassed for testing)
    $page = visit('/admin/tags')
         ->assertPathIs('/admin/tags')
         ->assertSee('Create Tag');

    // Create a new tag
    $page->click('Create Tag')
         ->assertPathIs('/admin/tags/create')
         ->assertSee('Create Tag')
         ->fill('name', 'Browser Test Tag')
         ->fill('slug', 'browser-test-tag')
         ->press('Create')
         ->assertPathIs('/admin/tags')
         ->wait(2)
         ->assertSee('Browser Test Tag');

    // Verify tag was created in database
    $tag = Tag::where('name', 'Browser Test Tag')->first();
    expect($tag)->not->toBeNull();
    expect($tag->slug)->toBe('browser-test-tag');

    // Edit the tag
    // $page->click("a[href*='/admin/tags/{$tag->id}/edit']")
    //      ->assertPathIs("/admin/tags/{$tag->id}/edit")
    //      ->assertSee('Edit Tag')
    //      ->assertValue('name', 'Browser Test Tag')
    //      ->assertValue('slug', 'browser-test-tag')
    //      ->fill('name', 'Browser Test Tag Updated')
    //      ->fill('slug', 'browser-test-tag-updated')
    //      ->press('Save')
    //      ->assertPathIs('/admin/tags')
    //      ->wait(2)
    //      ->assertSee('Browser Test Tag Updated')
    //      ->assertDontSee('Browser Test Tag');

    // Verify tag was updated in database
    // $tag->refresh();
    // expect($tag->name)->toBe('Browser Test Tag Updated');
    // expect($tag->slug)->toBe('browser-test-tag-updated');
    //    $page->screenshot();
})->skip('Browser tests require proper Dusk/browser testing setup with authentication');

// it('validates tag creation form fields', function () {
//     $user = User::factory()->create([
//         'email' => 'validation@example.com',
//         'password' => static::$password ??= Hash::make('password'),
//     ]);

//     $page = visit('/admin/tags')
//         ->assertPathIs('/admin/tags')
//         ->click('Create Tag')
//         ->assertPathIs('/admin/tags/create')
//         ->fill('name', '')
//         ->fill('slug', '')
//         ->press('Create')
//         ->assertPathIs('/admin/tags/create')
//         ->assertSee('The name field is required');
// });

// it('can navigate through tag management workflow', function () {
//     $user = User::factory()->create([
//         'email' => 'workflow@example.com',
//         'password' => static::$password ??= Hash::make('password'),
//     ]);

//     // Create some existing tags
//     $existingTag1 = Tag::factory()->create(['name' => 'Existing Tag 1']);
//     $existingTag2 = Tag::factory()->create(['name' => 'Existing Tag 2']);

//     $page = visit('/admin/tags')
//         ->assertPathIs('/admin/tags')
//         ->assertSee('Existing Tag 1')
//         ->assertSee('Existing Tag 2');

//     // Create new tag
//     $page->click('Create Tag')
//          ->fill('name', 'Workflow Test Tag')
//          ->fill('slug', 'workflow-test-tag')
//          ->press('Create')
//          ->assertPathIs('/admin/tags')
//          ->assertSee('Workflow Test Tag');

//     $newTag = Tag::where('name', 'Workflow Test Tag')->first();
//     expect($newTag)->not->toBeNull();

//     // Edit the newly created tag
//     $page->click("Edit", "tr:has(td:contains('Workflow Test Tag'))")
//          ->assertPathIs("/admin/tags/{$newTag->id}/edit")
//          ->fill('name', 'Workflow Test Tag Edited')
//          ->fill('slug', 'workflow-test-tag-edited')
//          ->press('Save')
//          ->assertPathIs('/admin/tags')
//          ->assertSee('Workflow Test Tag Edited')
//          ->assertDontSee('Workflow Test Tag');

//     // Verify final state
//     $newTag->refresh();
//     expect($newTag->name)->toBe('Workflow Test Tag Edited');
//     expect($newTag->slug)->toBe('workflow-test-tag-edited');
// });

// it('can access admin pages directly', function () {
//     $tag = Tag::factory()->create([
//         'name' => 'Test Tag',
//         'slug' => 'test-tag'
//     ]);

//     // Since authentication is bypassed for testing, we can directly access admin pages
//     $page = visit('/admin/tags');
//     $page->assertPathIs('/admin/tags')
//          ->assertSee('Test Tag');

//     $page = visit("/admin/tags/{$tag->id}/edit");
//     $page->assertPathIs("/admin/tags/{$tag->id}/edit")
//          ->assertSee('Edit Tag');
// });
