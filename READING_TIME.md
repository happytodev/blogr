# Estimated Reading Time

The blogr plugin now includes an estimated reading time feature to enhance the user experience.

## Features

- ⏱️ **Automatic display** of reading time on all pages
- 🕐 **Elegant clock icon** with Tailwind classes (hidden when disabled)
- ⚙️ **Customizable configuration** of reading speed
- 📝 **Customizable text format** for reading time
- 📱 **Responsive** and consistent across all devices
- **Enable/disab    le** reading time display

## Configuration

Modify the `config/blogr.php` file to adjust the reading speed and display options:

```php
'reading_speed' => [
    'words_per_minute' => 200, // Adjust according to your audience
],

'reading_time' => [
    'enabled' => true, // Enable/disable reading time display
    'text_format' => 'Reading time: {time}', // Text format with {time} placeholder
],
```

### Reading speed standards:
- **150 words/minute**: Slow readers
- **200 words/minute**: Average readers (default)
- **250 words/minute**: Fast readers
- **300 words/minute**: Very fast readers

### Text format examples:
- `'Reading time: {time}'` → "Reading time: 5 minutes"
- `'{time} to read'` → "5 minutes to read"
- `'⏱️ {time}'` → "⏱️ 5 minutes"
- `'Estimated: {time}'` → "Estimated: 5 minutes"

### Complete deactivation:
```php
'reading_time' => [
    'enabled' => false, // Completely hides both icon and text
],
```

## Usage in Views

The reading time is automatically displayed in:
- Blog homepage (`/blog`)
- Category pages (`/blog/category/{slug}`)
- Tag pages (`/blog/tag/{slug}`)
- Article detail pages (`/blog/{slug}`)

## Icon Customization

The clock icon is defined in `resources/views/components/clock-icon.blade.php`:

```blade
<svg class="inline w-4 h-4 mr-1 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
</svg>
```

You can modify the Tailwind classes according to your needs.
