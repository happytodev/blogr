<?php

namespace Happytodev\Blogr\Http\Controllers;

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;

class CmsPageController extends Controller
{
    /**
     * Display the homepage CMS page
     *
     * @param string|null $locale The locale (optional if locales disabled)
     * @return \Illuminate\View\View
     */
    public function showHomepage(Request $request, ?string $locale = null)
    {
        $localesEnabled = config('blogr.locales.enabled', false);
        $defaultLocale = config('blogr.locales.default', 'en');
        
        // Determine the locale to use
        $currentLocale = $locale ?? $defaultLocale;
        
        // Set application locale
        if ($localesEnabled) {
            App::setLocale($currentLocale);
        }
        
        // Find the homepage page
        $page = CmsPage::homepage()->published()->first();
        
        if (!$page) {
            abort(404, 'Homepage not found or not published');
        }
        
        // Get translation for current locale or fallback to default
        $translation = $page->translations()
            ->where('locale', $currentLocale)
            ->first();
        
        // Fallback to default locale if not found
        if (!$translation && $localesEnabled && $currentLocale !== $defaultLocale) {
            $translation = $page->translations()
                ->where('locale', $defaultLocale)
                ->first();
        }
        
        if (!$translation) {
            abort(404, 'Homepage translation not found');
        }
        
        // Prepare view data
        $viewData = [
            'page' => $page,
            'translation' => $translation,
            'title' => $translation->title,
            'content' => $translation->content,
            'blocks' => $translation->blocks ?? [], // Blocks are now per-translation
            'seoTitle' => $translation->seo_title ?? $translation->title,
            'seoDescription' => $translation->seo_description ?? $translation->excerpt,
            'seoKeywords' => $translation->seo_keywords,
            'currentLocale' => $currentLocale,
            'availableLocales' => $page->availableLocales(),
        ];
        
        // DEBUG: Uncomment to debug blocks
        // dd([
        //     'translation_blocks' => $translation->blocks,
        //     'viewData_blocks' => $viewData['blocks'],
        //     'is_empty' => empty($viewData['blocks']),
        //     'count' => count($viewData['blocks'] ?? []),
        // ]);
        
        // Return view based on template
        return view($this->getViewForTemplate($page->template->value), $viewData);
    }

    /**
     * Display a CMS page by slug and locale
     *
     * @param string|null $localeOrSlug First route parameter (locale if locales enabled, slug otherwise)
     * @param string|null $slug Second route parameter (slug if locales enabled, null otherwise)
     * @return \Illuminate\View\View
     */
    public function show(Request $request, ?string $localeOrSlug = null, ?string $slug = null)
    {
        $localesEnabled = config('blogr.locales.enabled', false);
        $defaultLocale = config('blogr.locales.default', 'en');
        
        // Determine locale and slug based on whether locales are enabled
        if ($localesEnabled && $slug !== null) {
            // Route is /{locale}/{slug}
            $currentLocale = $localeOrSlug;
            $actualSlug = $slug;
        } else {
            // Route is /{slug} or /{prefix}/{slug}
            $currentLocale = $defaultLocale;
            $actualSlug = $localeOrSlug;
        }
        
        // Set application locale
        if ($localesEnabled) {
            App::setLocale($currentLocale);
        }
        
        // Find the translation by slug and locale
        $translation = CmsPageTranslation::where('slug', $actualSlug)
            ->where('locale', $currentLocale)
            ->first();
        
        // If no translation found, try fallback to default locale
        if (!$translation && $localesEnabled && $currentLocale !== $defaultLocale) {
            $translation = CmsPageTranslation::where('slug', $actualSlug)
                ->where('locale', $defaultLocale)
                ->first();
        }
        
        // If still no translation, try to find by main page slug (for backward compatibility)
        if (!$translation) {
            $page = CmsPage::where('slug', $actualSlug)->first();
            
            if ($page) {
                // Get translation for current locale
                $translation = $page->translations()
                    ->where('locale', $currentLocale)
                    ->first();
                
                // Fallback to default locale
                if (!$translation && $localesEnabled) {
                    $translation = $page->translations()
                        ->where('locale', $defaultLocale)
                        ->first();
                }
            }
        }
        
        // 404 if no translation found
        if (!$translation) {
            abort(404, 'Page not found');
        }
        
        $page = $translation->page;
        
        // Check if page is published
        if (!$page->is_published) {
            abort(404, 'Page not published');
        }
        
        // Check if publish date is in the future
        if ($page->published_at && $page->published_at->isFuture()) {
            abort(404, 'Page not yet published');
        }
        
        // Determine the view based on template
        $viewName = $this->getViewForTemplate($page->template->value);
        
        // Return view with page and translation data
        return view($viewName, [
            'page' => $page,
            'translation' => $translation,
            'title' => $translation->title,
            'content' => $translation->content,
            'blocks' => $translation->blocks ?? [], // Blocks are now per-translation
            'template' => $page->template,
            'locale' => $currentLocale,
            'seoTitle' => $translation->seo_title ?? $translation->title,
            'seoDescription' => $translation->seo_description ?? $translation->excerpt,
            'seoKeywords' => $translation->seo_keywords,
        ]);
    }
    
    /**
     * Get the view name based on template
     *
     * @param string $template
     * @return string
     */
    protected function getViewForTemplate(string $template): string
    {
        $templateViews = [
            'default' => 'blogr::cms.pages.default',
            'landing' => 'blogr::cms.pages.landing',
            'contact' => 'blogr::cms.pages.contact',
            'about' => 'blogr::cms.pages.about',
            'pricing' => 'blogr::cms.pages.pricing',
            'faq' => 'blogr::cms.pages.faq',
            'custom' => 'blogr::cms.pages.custom',
        ];
        
        return $templateViews[strtolower($template)] ?? 'blogr::cms.pages.default';
    }
}
