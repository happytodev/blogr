<?php

use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Tests\Database\Factories\CategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('validates slug uniqueness at database level', function () {
    Category::create(['name' => 'First Category', 'slug' => 'first-category']);
    
    $this->expectException(\Exception::class);
    Category::create(['name' => 'Second Category', 'slug' => 'first-category']);
});

it('generates slug from name in boot method', function () {
    $category = Category::create(['name' => 'Test Category']);
    
    expect($category->slug)->toBe('test-category');
});

it('preserves existing slug when provided', function () {
    $category = Category::create(['name' => 'Test Category', 'slug' => 'custom-slug']);
    
    expect($category->slug)->toBe('custom-slug');
});

it('has many posts relationship', function () {
    $category = CategoryFactory::new()->create();
    
    expect($category->posts())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('can create category with factory', function () {
    $category = CategoryFactory::new()->create();
    
    expect($category)->toBeInstanceOf(Category::class);
    expect($category->name)->toBeString();
    expect($category->slug)->toBeString();
    expect($category->is_default)->toBeBool();
});
