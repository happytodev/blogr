# CMS Block Dynamic Links

## Overview

CMS blocks (Hero, CTA, Pricing) now support dynamic links with multiple options:

- **External URL**: Direct links to external websites
- **Blog Home**: Link to the blog homepage
- **Category**: Link to a specific blog category
- **CMS Page**: Link to another CMS page

## Implementation

### Form Configuration

In `CmsBlockBuilder.php`, blocks use the `LinkFieldsTrait` to provide consistent link configuration:

```php
use Happytodev\Blogr\Filament\Forms\LinkFieldsTrait;

class CmsBlockBuilder {
    use LinkFieldsTrait;
    
    // Hero block CTA
    ...self::getLinkFieldsSchema(
        linkTypeFieldName: 'cta_link_type',
        urlFieldName: 'cta_url',
        categoryIdFieldName: 'cta_category_id',
        cmsPageIdFieldName: 'cta_cms_page_id',
        includeBlogHome: true
    ),
}
```

### Block Data Structure

Each block stores link information with these fields:

```php
'cta_link_type' => 'external',        // Options: 'external', 'blog', 'category', 'cms_page'
'cta_url' => 'https://example.com',   // Used when link_type is 'external'
'cta_category_id' => 5,               // Used when link_type is 'category'
'cta_cms_page_id' => 3,               // Used when link_type is 'cms_page'
```

### View Resolution

In blade views, use the `LinkResolver` helper to convert link data into actual URLs:

```blade
@php
use Happytodev\Blogr\Helpers\LinkResolver;

$ctaUrl = LinkResolver::resolve(
    $data, 
    'cta_link_type',      // Link type field name
    'cta_url',             // URL field name
    'cta_category_id',     // Category ID field name
    'cta_cms_page_id'      // CMS page ID field name
);
@endphp

<a href="{{ $ctaUrl }}">Click me</a>
```

## Block Examples

### Hero Block

```php
[
    'type' => 'hero',
    'data' => [
        'title' => 'Welcome',
        'cta_text' => 'Start Blog',
        'cta_link_type' => 'blog',           // Link to blog homepage
        'cta_url' => null,
        'cta_category_id' => null,
        'cta_cms_page_id' => null,
    ]
]

// Or with external link:
[
    'type' => 'hero',
    'data' => [
        'cta_link_type' => 'external',
        'cta_url' => 'https://example.com',
        'cta_category_id' => null,
        'cta_cms_page_id' => null,
    ]
]

// Or with category link:
[
    'type' => 'hero',
    'data' => [
        'cta_link_type' => 'category',
        'cta_url' => null,
        'cta_category_id' => 5,              // Category ID
        'cta_cms_page_id' => null,
    ]
]
```

### CTA Block

Same structure with `button_link_type`, `button_url`, `button_category_id`, `button_cms_page_id`.

### Pricing Block

Each plan item has its own link configuration with `cta_link_type`, `cta_url`, etc.

## Backward Compatibility

Blocks created before this feature only have `cta_url` (or `button_url`/etc). The `LinkResolver` defaults to `external` link type when the `*_link_type` field is missing, ensuring old blocks continue to work seamlessly.

## Files Modified

- `src/Filament/Forms/LinkFieldsTrait.php` - Reusable link field components
- `src/Helpers/LinkResolver.php` - Resolve links based on type
- `src/Filament/Resources/CmsPages/CmsBlockBuilder.php` - Updated all link fields
- `resources/views/components/blocks/hero.blade.php` - Use LinkResolver
- `resources/views/components/blocks/cta.blade.php` - Use LinkResolver
- `resources/views/components/blocks/pricing.blade.php` - Use LinkResolver

## Usage in Custom Blocks

To add dynamic links to custom blocks:

1. **In the form builder:**
   ```php
   use Happytodev\Blogr\Filament\Forms\LinkFieldsTrait;
   
   class MyBlockBuilder {
       use LinkFieldsTrait;
       
       ...self::getLinkFieldsSchema(
           linkTypeFieldName: 'my_link_type',
           urlFieldName: 'my_url',
           categoryIdFieldName: 'my_category_id',
           cmsPageIdFieldName: 'my_cms_page_id',
       )
   }
   ```

2. **In the blade view:**
   ```blade
   @php
   use Happytodev\Blogr\Helpers\LinkResolver;
   
   $url = LinkResolver::resolve($data, 'my_link_type', 'my_url', 'my_category_id', 'my_cms_page_id');
   @endphp
   ```

## API Endpoints

When exporting/importing CMS pages, all link fields are preserved:

```json
{
  "blocks": [
    {
      "type": "hero",
      "data": {
        "cta_link_type": "category",
        "cta_category_id": 5,
        "cta_url": null,
        "cta_cms_page_id": null
      }
    }
  ]
}
```
