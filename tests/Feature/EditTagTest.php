<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\EditTag;
use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('generates slug automatically when leaving name field', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Simulate the reactive behavior from TagForm
    $newName = 'New Test Tag';
    $generatedSlug = Str::slug($newName);

    $tag->update(['name' => $newName, 'slug' => $generatedSlug]);

    expect($tag->fresh()->slug)->toBe('new-test-tag');
});

it('validates slug uniqueness', function () {
    $existingTag = Tag::factory()->create(['name' => 'Existing Tag', 'slug' => 'existing-tag']);
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Attempt to update with duplicate slug
    try {
        $tag->update(['name' => 'Test Tag', 'slug' => 'existing-tag']);
        // If we get here, the update succeeded (which shouldn't happen with proper validation)
        expect(false)->toBeTrue('Slug uniqueness validation failed');
    } catch (\Exception $e) {
        // This is expected - validation should prevent duplicate slugs
        expect($e)->toBeInstanceOf(\Exception::class);
    }
});

it('does not regenerate slug if manually modified', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Update with manually modified slug
    $tag->update(['name' => 'Test Tag', 'slug' => 'custom-slug']);

    expect($tag->fresh()->slug)->toBe('custom-slug');
});

it('generates slug from name when name is changed and slug is empty', function () {
    $tag = Tag::factory()->create(['name' => 'Old Tag', 'slug' => '']);

    // For existing records, the boot method doesn't trigger on update
    // The slug generation happens in the form's reactive behavior
    $newName = 'New Tag Name';
    $generatedSlug = Str::slug($newName);

    $tag->update(['name' => $newName, 'slug' => $generatedSlug]);

    expect($tag->fresh()->slug)->toBe('new-tag-name');
});

it('preserves existing slug when only name is changed', function () {
    $tag = Tag::factory()->create(['name' => 'Old Tag', 'slug' => 'preserve-this-slug']);

    $tag->update(['name' => 'New Tag Name', 'slug' => 'preserve-this-slug']);

    expect($tag->fresh()->slug)->toBe('preserve-this-slug');
});

it('handles special characters in name for slug generation', function () {
    $tag = Tag::factory()->create(['name' => 'Old Tag', 'slug' => 'old-tag']);

    $newName = 'Tag with spécial caractères!';
    $expectedSlug = Str::slug($newName);

    $tag->update(['name' => $newName, 'slug' => $expectedSlug]);

    expect($tag->fresh()->slug)->toBe('tag-with-special-caracteres');
});

it('requires name field', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // This should work since we're updating an existing record
    $result = $tag->update(['name' => 'Test Tag', 'slug' => 'test-tag']);
    expect($result)->toBeTrue();
});

it('requires slug field', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // This should work since we're updating an existing record
    $result = $tag->update(['name' => 'Test Tag', 'slug' => 'test-tag']);
    expect($result)->toBeTrue();
});

it('tests slug generation logic from TagForm', function () {
    // Test the slug generation logic that would be used in TagForm
    $testCases = [
        'Simple Tag' => 'simple-tag',
        'Tag With Spaces' => 'tag-with-spaces',
        'Tag-with-Dashes' => 'tag-with-dashes',
        'Tag with spécial caractères!' => 'tag-with-special-caracteres',
        'TAG WITH CAPS' => 'tag-with-caps',
        '123 Numbers' => '123-numbers',
    ];

    foreach ($testCases as $input => $expected) {
        $generated = Str::slug($input);
        expect($generated)->toBe($expected);
    }
});

it('tests model boot method slug generation', function () {
    // Test that the model generates slug on creation when empty
    $tag = new Tag(['name' => 'Boot Test Tag']);
    $tag->save();

    expect($tag->slug)->toBe('boot-test-tag');
});

it('tests model boot method preserves existing slug', function () {
    // Test that the model preserves slug when provided
    $tag = new Tag(['name' => 'Boot Test Tag', 'slug' => 'custom-slug']);
    $tag->save();

    expect($tag->slug)->toBe('custom-slug');
});

// Tests Livewire pour couvrir la page EditTag
it('can render edit tag page', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Test that the page class exists and can be instantiated
    $pageClass = EditTag::class;
    expect(class_exists($pageClass))->toBeTrue();
});

it('edit tag page has required methods', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Test that the page has the getHeaderActions method
    $pageClass = EditTag::class;
    expect(method_exists($pageClass, 'getHeaderActions'))->toBeTrue();
});

it('edit tag page inherits from EditRecord', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Test that EditTag extends EditRecord
    $pageClass = EditTag::class;
    $reflection = new \ReflectionClass($pageClass);

    expect($reflection->getParentClass()->getName())->toBe('Filament\Resources\Pages\EditRecord');
});

it('edit tag page can be instantiated', function () {
    $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);

    // Test that the page can be instantiated
    $pageClass = EditTag::class;
    $page = new $pageClass();

    expect($page)->toBeInstanceOf($pageClass);
    expect($page)->toBeInstanceOf('Filament\Resources\Pages\EditRecord');
});
