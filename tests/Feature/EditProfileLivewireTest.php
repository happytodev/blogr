<?php

use Happytodev\Blogr\Filament\Pages\EditProfile;
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
        'avatar' => 'avatars/existing-avatar.jpg',
        'bio' => 'Original bio',
    ]);
    
    $this->actingAs($this->user);
});

test('it can update user name without touching avatar', function () {
    $component = Livewire::test(EditProfile::class);
    
    // Set new name but don't touch avatar
    $component->set('name', 'Jane Smith');
    $component->call('save');
    
    $this->user->refresh();
    expect($this->user->name)->toBe('Jane Smith');
    expect($this->user->avatar)->toBe('avatars/existing-avatar.jpg'); // Should preserve existing avatar
});

test('it handles empty avatar array', function () {
    // This is the critical bug test
    $this->user->update(['avatar' => null]);
    
    $component = Livewire::test(EditProfile::class);
    $component->set('name', 'Jane Smith');
    $component->call('save');
    
    $this->user->refresh();
    expect($this->user->name)->toBe('Jane Smith');
    expect($this->user->avatar)->toBeNull();
});

test('it does not save when name is empty', function () {
    $originalName = $this->user->name;
    
    $component = Livewire::test(EditProfile::class);
    $component->set('name', '');
    $component->call('save');
    
    $this->user->refresh();
    expect($this->user->name)->toBe($originalName); // Name should not have changed
});
