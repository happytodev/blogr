# Quick Category Creation

## Overview

The Quick Category Creation feature allows users to create new categories directly from the blog post creation/edit form, without having to navigate away to the Categories management page.

## How to Use

1. **Creating a Post**: When creating or editing a blog post, locate the "Category" dropdown field
2. **Create New Category**: 
   - Click on the category dropdown
   - Scroll to the bottom and click on "Create new category" (or similar button depending on your Filament version)
3. **Fill the Form**: A modal/slideout will appear with the following fields:
   - **Category Name** (required): The main name of the category
   - **Slug** (required, unique): Auto-generated from the name, but can be manually edited
   - **Set as Default Category**: Toggle to make this category the default for new posts

4. **Submit**: After filling the form, the category will be created immediately and automatically selected in the post form

## Features

### Auto-Generated Slug
When you type a category name, the slug is automatically generated:
- Input: `My Awesome Category`
- Generated slug: `my-awesome-category`

You can override this by manually editing the slug field.

### Unique Validation
The slug must be unique across all categories. If you try to create a category with an existing slug, you'll see a validation error.

### Default Category
You can set any category as the default category by toggling the "Set as Default Category" switch. This category will be automatically selected when creating new posts.

## Technical Implementation

### Form Configuration

The quick creation form is defined in `BlogPostForm.php`:

```php
Select::make('category_id')
    ->label('Category')
    ->createOptionForm([
        TextInput::make('name')
            ->label('Category Name')
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Set $set, ?string $state) {
                if ($state) {
                    $set('slug', Str::slug($state));
                }
            }),
        
        TextInput::make('slug')
            ->label('Slug')
            ->required()
            ->unique('categories', 'slug')
            ->maxLength(255),
        
        Toggle::make('is_default')
            ->label('Set as Default Category')
            ->default(false),
    ])
    ->createOptionUsing(function (array $data): int {
        $category = Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'is_default' => $data['is_default'] ?? false,
        ]);
        
        return $category->id;
    })
```

### Model Auto-Generation

The `Category` model automatically generates slugs if they're empty:

```php
public static function boot()
{
    parent::boot();

    static::creating(function ($category) {
        if (empty($category->slug)) {
            $category->slug = Str::slug($category->name);
        }
    });
}
```

## Testing

Tests are available in `tests/Feature/QuickCategoryCreationTest.php`:

```bash
./vendor/bin/pest tests/Feature/QuickCategoryCreationTest.php
```

Tests cover:
- Creating a category with all required fields
- Auto-generation of slugs
- Setting default categories
- Validation of required fields
- Uniqueness constraint on slugs

## Benefits

1. **Improved Workflow**: No need to leave the post creation page
2. **Faster Content Creation**: Create categories on-the-fly as you write
3. **Better UX**: Streamlined process for writers and content creators
4. **Consistent with Tags**: Same pattern as the tag creation feature

## Future Enhancements

- Add translation support in the quick creation form
- Add color picker for category colors (if implemented)
- Add icon selection for categories
- Batch category creation

## Related Features

- **Category Management**: Full CRUD available at `/admin/categories`
- **Category Translations**: Manage translations via the Category edit page
- **Tag Quick Creation**: Similar feature available for tags in the same form
