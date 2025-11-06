<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Happytodev\Blogr\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'test-author',
        'bio' => ['en' => 'Short bio'],
    ]);

    $this->actingAs($this->user);
});

test('bio field accepts extended length up to 2000 characters', function () {
    $longBio = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 35); // ~1960 chars
    
    expect(strlen($longBio))->toBeGreaterThan(1500)
        ->and(strlen($longBio))->toBeLessThanOrEqual(2000);

    Livewire::test(EditProfile::class)
        ->fillForm([
            'bio.en' => $longBio,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->user->refresh();
    expect($this->user->bio['en'])->toBe($longBio);
})->skip('Requires Livewire bindings - EditProfile Livewire component');

test('bio field uses MarkdownEditor component instead of Textarea', function () {
    // Test by checking the source code uses MarkdownEditor
    $filePath = __DIR__ . '/../../src/Filament/Pages/Auth/EditProfile.php';
    $content = file_get_contents($filePath);
    
    expect($content)->toContain('MarkdownEditor::make')
        ->and($content)->not->toContain('Textarea::make("bio.');
});

test('bio field with multilingual support uses MarkdownEditor for each locale', function () {
    // This test verifies the implementation uses MarkdownEditor in the loop
    $filePath = __DIR__ . '/../../src/Filament/Pages/Auth/EditProfile.php';
    $content = file_get_contents($filePath);
    
    expect($content)->toContain('MarkdownEditor::make("bio.')
        ->and($content)->toContain('use Filament\Forms\Components\MarkdownEditor;');
});

test('profile edit page has wider max-width on large screens', function () {
    // Test by checking if the class implements a wider max width using Width enum
    $filePath = __DIR__ . '/../../src/Filament/Pages/Auth/EditProfile.php';
    $content = file_get_contents($filePath);
    
    // Should define a wider max width (5xl, 6xl, 7xl, or full)
    $hasWideMaxWidth = str_contains($content, 'Width::FiveExtraLarge') ||
                       str_contains($content, 'Width::SixExtraLarge') ||
                       str_contains($content, 'Width::SevenExtraLarge') ||
                       str_contains($content, 'Width::Full');
    
    expect($hasWideMaxWidth)->toBeTrue();
});

test('markdown in bio is preserved when saved', function () {
    $markdownBio = <<<'MD'
# About Me

I'm a **passionate** developer who loves:

- Writing clean code
- Learning new technologies
- Sharing knowledge

Check out my [website](https://example.com)!
MD;

    Livewire::test(EditProfile::class)
        ->fillForm([
            'bio.en' => $markdownBio,
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->user->refresh();
    expect($this->user->bio['en'])->toBe($markdownBio)
        ->and($this->user->bio['en'])->toContain('# About Me')
        ->and($this->user->bio['en'])->toContain('**passionate**')
        ->and($this->user->bio['en'])->toContain('[website]');
})->skip('Requires Livewire bindings - EditProfile Livewire component');
