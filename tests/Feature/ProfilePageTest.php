<?php

use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
        'avatar' => null,
        'bio' => null,
    ]);
    
    $this->actingAs($this->user);
});

test('profile page loads successfully', function () {
    Livewire::test(EditProfile::class)
        ->assertSuccessful();
});

test('can update bio', function () {
    Livewire::test(EditProfile::class)
        ->fillForm([
            'bio' => 'This is my new bio',
        ])
        ->call('save')
        ->assertHasNoErrors();
    
    $this->user->refresh();
    expect($this->user->bio)->toBe('This is my new bio');
});

test('can update avatar', function () {
    Storage::fake('public');
    
    $file = UploadedFile::fake()->image('avatar.jpg');
    
    Livewire::test(EditProfile::class)
        ->fillForm([
            'avatar' => $file,
        ])
        ->call('save')
        ->assertHasNoErrors();
    
    $this->user->refresh();
    expect($this->user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($this->user->avatar);
});

test('can update name and email from base form', function () {
    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ])
        ->call('save')
        ->assertHasNoErrors();
    
    $this->user->refresh();
    expect($this->user->name)->toBe('Jane Smith');
    expect($this->user->email)->toBe('jane@example.com');
});

test('bio field accepts null', function () {
    $this->user->update(['bio' => 'Original bio']);
    
    Livewire::test(EditProfile::class)
        ->fillForm([
            'bio' => null,
        ])
        ->call('save')
        ->assertHasNoErrors();
    
    $this->user->refresh();
    expect($this->user->bio)->toBeNull();
});

test('name is required', function () {
    $originalName = $this->user->name;
    
    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => '',
        ])
        ->call('save');
    
    $this->user->refresh();
    expect($this->user->name)->toBe($originalName);
});

test('email is required', function () {
    $originalEmail = $this->user->email;
    
    Livewire::test(EditProfile::class)
        ->fillForm([
            'email' => '',
        ])
        ->call('save');
    
    $this->user->refresh();
    expect($this->user->email)->toBe($originalEmail);
});

test('existing bio is loaded in form', function () {
    $this->user->update(['bio' => 'Existing bio text']);
    
    Livewire::test(EditProfile::class)
        ->assertFormSet([
            'bio' => 'Existing bio text',
        ]);
});

test('existing avatar is loaded in form', function () {
    Storage::fake('public');
    
    // Create actual file so it exists
    $file = UploadedFile::fake()->image('test-avatar.jpg');
    Storage::disk('public')->put('avatars/test-avatar.jpg', $file->getContent());
    
    $this->user->update(['avatar' => 'avatars/test-avatar.jpg']);
    
    $component = Livewire::test(EditProfile::class);
    
    // Check that the avatar value is set in the form
    // FileUpload stores files as array with UUID keys
    $formData = $component->get('data');
    expect($formData['avatar'])->toBeArray();
    expect(reset($formData['avatar']))->toBe('avatars/test-avatar.jpg');
});

test('existing name and email are loaded in form', function () {
    Livewire::test(EditProfile::class)
        ->assertFormSet([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
});
