# Language Switcher - CMS Page Slug Translation Fix

## 🐛 Bug Report

### Problem
When switching language on a CMS page with translated slugs, the URL remained the same instead of switching to the translated slug.

**Example:**
- User on `/fr/nous-contacter` (French contact page)
- Clicks English language switcher
- Expected: `/en/contact-us`
- Actual (Bug): `/en/nous-contacter` ❌

### Root Cause
The language switcher component in `navigation.blade.php` was changing only the `locale` parameter in the route, but keeping the same `slug`. For CMS pages with different slugs per language, this resulted in 404 errors or wrong content.

## ✅ Solution Implemented

### 1. Created Non-Regression Tests
**File:** `tests/Localized/LanguageSwitcherCmsPageTest.php`

Three comprehensive tests to prevent regression:
- ✅ `test_switching_language_on_cms_page_uses_translated_slug` - Main test (was failing, now passes)
- ✅ `test_switching_same_language_on_cms_page_keeps_same_slug` - Same language keeps URL
- ✅ `test_language_switcher_works_for_cms_pages_with_same_slug_all_languages` - Works with identical slugs

### 2. Code Changes

#### File: `resources/views/components/navigation.blade.php`
- **Added prop:** `$cmsPageId` to receive the current CMS page ID
- **Updated logic:** Language switcher now queries `CmsPageTranslation` to get the translated slug for the target locale
- **Smart handling:** If translation exists, uses the translated slug; otherwise, keeps current slug

```blade
// Get the translated slug for this page in the target locale
if ($currentRouteName === 'cms.page.show' && $cmsPageId) {
    $cmsTranslation = \Happytodev\Blogr\Models\CmsPageTranslation::where('cms_page_id', $cmsPageId)
        ->where('locale', $locale)
        ->first();
    
    if ($cmsTranslation) {
        $currentParams['slug'] = $cmsTranslation->slug;
    }
}
```

#### File: `resources/views/layouts/blog.blade.php`
- **Modified:** Navigation include now passes `cmsPageId => $page?->id ?? null`
- This allows the component to know which CMS page is currently being viewed

```blade
@include('blogr::components.navigation', [
    'currentLocale' => $currentLocale ?? config('blogr.locales.default', 'en'),
    'availableLocales' => $availableLocales ?? config('blogr.locales.available', ['en']),
    'cmsPageId' => $page?->id ?? null,
])
```

## 🧪 Test Results

### Before Fix
```
FAILED Tests\Localized\LanguageSwitcherCmsPageTest
- Test: switching_language_on_cms_page_uses_translated_slug
- Expected: /en/contact-us in HTML
- Actual: /en/nous-contacter in HTML
```

### After Fix
```
✅ Tests:  3 passed (8 assertions)
✅ Full Suite: 654 passed, 57 skipped (1886 assertions)
```

## 🔄 Behavior Changes

### Scenario 1: Different Slugs per Language ✅ FIXED
- Page: "Contact" (cms_page_id: 2)
- EN translation: `slug = "contact-us"`
- FR translation: `slug = "nous-contacter"`
- **Before:** `/fr/nous-contacter` → English link → `/en/nous-contacter` (404) ❌
- **After:** `/fr/nous-contacter` → English link → `/en/contact-us` (works) ✅

### Scenario 2: Same Slug All Languages ✅ WORKS
- Page: "About" (cms_page_id: 3)
- EN translation: `slug = "about"`
- FR translation: `slug = "about"`
- **Before:** `/fr/about` → English link → `/en/about` ✅
- **After:** `/fr/about` → English link → `/en/about` ✅ (unchanged, still works)

## 📦 Files Modified

1. `resources/views/components/navigation.blade.php` - Language switcher logic
2. `resources/views/layouts/blog.blade.php` - Pass CMS page ID to navigation
3. `tests/Localized/LanguageSwitcherCmsPageTest.php` - New non-regression tests (3 tests)

## 🚀 Deployment Notes

- ✅ All existing tests pass (654 tests)
- ✅ New tests added for regression prevention
- ✅ Backward compatible (works with blog posts, non-CMS pages)
- ✅ Graceful degradation (if `cms_page_id` is null, falls back to current behavior)

## 📋 Checklist

- [x] Created comprehensive test suite (TDD approach)
- [x] Identified root cause
- [x] Implemented fix in navigation component
- [x] Updated layout to pass required data
- [x] All tests passing (including regression tests)
- [x] Applied changes to published views in app
- [x] Verified backward compatibility
