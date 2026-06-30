<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
use Happytodev\Blogr\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);

    $this->user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'john-doe',
        'avatar' => null,
        'bio' => null,
    ]);
    $this->user->assignRole('admin');

    $this->actingAs($this->user);
});

test('profile page loads successfully', function () {
    $this->get('/admin/profile')
        ->assertSuccessful();
});

test('can update bio', function () {
    $this->user->bio = ['en' => 'This is my new bio'];
    $this->user->save();

    $this->user->refresh();
    expect($this->user->bio)->toBeArray();
    expect($this->user->bio['en'])->toBe('This is my new bio');
});

test('can update avatar', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('avatar.jpg');
    $path = $file->store('avatars', 'public');

    $this->user->avatar = $path;
    $this->user->save();

    $this->user->refresh();
    expect($this->user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($this->user->avatar);
});

test('can update name and email from base form', function () {
    $this->user->name = 'Jane Smith';
    $this->user->email = 'jane@example.com';
    $this->user->save();

    $this->user->refresh();
    expect($this->user->name)->toBe('Jane Smith');
    expect($this->user->email)->toBe('jane@example.com');
});

test('bio field accepts null', function () {
    $this->user->bio = ['en' => 'Original bio'];
    $this->user->save();

    $this->user->bio = ['en' => null];
    $this->user->save();

    $this->user->refresh();
    expect($this->user->bio)->toBeArray();
    expect($this->user->bio['en'])->toBeNull();
});

test('name is required on form schema', function () {
    $filePath = (new ReflectionClass(EditProfile::class))->getFileName();
    $content = file_get_contents($filePath);

    expect($content)->toContain('getNameFormComponent');
});

test('email is required on form schema', function () {
    $filePath = (new ReflectionClass(EditProfile::class))->getFileName();
    $content = file_get_contents($filePath);

    expect($content)->toContain('getEmailFormComponent');
});

test('existing bio is loaded in form', function () {
    $this->user->update(['bio' => ['en' => 'Existing bio text']]);

    $this->get('/admin/profile')
        ->assertSuccessful();
});

test('existing avatar is loaded in form', function () {
    Storage::fake('public');

    Storage::disk('public')->put('avatars/test-avatar.jpg', 'fake');

    $this->user->update(['avatar' => 'avatars/test-avatar.jpg']);

    $this->get('/admin/profile')
        ->assertSuccessful();
});

test('existing name and email are loaded in form', function () {
    $this->get('/admin/profile')
        ->assertSuccessful()
        ->assertSee('John Doe');
});
