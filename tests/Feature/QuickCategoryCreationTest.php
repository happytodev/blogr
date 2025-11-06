<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Category::create([
        'name' => 'Default',
        'slug' => 'default',
        'is_default' => true,
    ]);
});

it('can create a category with quick creation form data', function () {
    $categoryData = [
        'name' => 'New Test Category',
        'slug' => 'new-test-category',
        'is_default' => false,
    ];
    
    $category = Category::create($categoryData);
    
    expect($category)->toBeInstanceOf(Category::class);
    expect($category->name)->toBe('New Test Category');
    expect($category->slug)->toBe('new-test-category');
    expect($category->is_default)->toBeFalse();
});

it('auto-generates slug from name when creating category', function () {
    $category = Category::create([
        'name' => 'My Awesome Category',
        'slug' => '',
    ]);
    
    expect($category->slug)->toBe('my-awesome-category');
});
