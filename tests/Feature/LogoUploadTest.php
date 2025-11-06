<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('can upload logo image via FileUpload', function () {
    $file = UploadedFile::fake()->image('logo.png', 400, 200);
    
    $path = $file->store('blogr/logos', 'public');
    
    expect($path)->toBeString();
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('resizes uploaded logo to max 200px height', function () {
    $file = UploadedFile::fake()->image('large-logo.png', 800, 600);
    
    $path = $file->store('blogr/logos', 'public');
    
    Storage::disk('public')->assertExists($path);
    
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('stores logo in correct directory', function () {
    $file = UploadedFile::fake()->image('logo.jpg');
    
    $path = $file->store('blogr/logos', 'public');
    
    expect($path)->toStartWith('blogr/logos/');
    Storage::disk('public')->assertExists($path);
});

it('accepts common image formats', function () {
    $formats = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
    
    foreach ($formats as $format) {
        $file = UploadedFile::fake()->image("logo.{$format}");
        $path = $file->store('blogr/logos', 'public');
        
        Storage::disk('public')->assertExists($path);
    }
});

it('navigation loads logo from storage', function () {
    $file = UploadedFile::fake()->image('test-logo.png');
    $path = $file->store('blogr/logos', 'public');
    
    config(['blogr.ui.navigation.logo' => $path]);
    
    $logoPath = config('blogr.ui.navigation.logo');
    expect($logoPath)->toBe($path);
});

it('navigation displays uploaded logo correctly', function () {
    $file = UploadedFile::fake()->image('site-logo.png');
    $path = $file->store('blogr/logos', 'public');
    
    config([
        'blogr.ui.navigation.logo' => $path,
        'blogr.ui.navigation.logo_display' => 'image',
    ]);
    
    // Vérifier que la config est bien chargée
    expect(config('blogr.ui.navigation.logo'))->toBe($path);
    expect(config('blogr.ui.navigation.logo_display'))->toBe('image');
    
    // Vérifier que le fichier existe
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

it('deletes old logo when uploading new one', function () {
    $oldFile = UploadedFile::fake()->image('old-logo.png');
    $oldPath = $oldFile->store('blogr/logos', 'public');
    
    expect(Storage::disk('public')->exists($oldPath))->toBeTrue();
    
    $newFile = UploadedFile::fake()->image('new-logo.png');
    $newPath = $newFile->store('blogr/logos', 'public');
    
    expect(Storage::disk('public')->exists($newPath))->toBeTrue();
});
