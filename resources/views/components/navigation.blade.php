@props(['currentLocale' => config('blogr.locales.default', 'en'), 'availableLocales' => config('blogr.locales.available', ['en']), 'cmsPageId' => null])

@php
    // Helper function to get label for current locale
    if (!function_exists('getMenuLabel')) {
        function getMenuLabel($item, $locale) {
            if (isset($item['labels']) && is_array($item['labels'])) {
                foreach ($item['labels'] as $labelData) {
                    if (isset($labelData['locale']) && $labelData['locale'] === $locale) {
                        return $labelData['label'] ?? 'Menu Item';
                    }
                }
                // Fallback to first label if locale not found
                return $item['labels'][0]['label'] ?? 'Menu Item';
            }
            // Legacy support: single label field
            return $item['label'] ?? 'Menu Item';
        }
    }

    // Helper function to generate URL
    if (!function_exists('getMenuUrl')) {
        function getMenuUrl($item, $locale) {
            $url = '#';
            $isActive = false;
            
            switch($item['type'] ?? 'external') {
                case 'external':
                    $url = $item['url'] ?? '#';
                    break;
                case 'blog':
                    $url = route('blog.index', ['locale' => $locale]);
                    $isActive = request()->route()->getName() === 'blog.index';
                    break;
                case 'category':
                    if (!empty($item['category_id'])) {
                        $category = \Happytodev\Blogr\Models\Category::find($item['category_id']);
                        if ($category) {
                            $translation = $category->translations()->where('locale', $locale)->first();
                            if ($translation) {
                                $url = route('blog.category', ['locale' => $locale, 'categorySlug' => $translation->slug]);
                                $isActive = request()->route()->getName() === 'blog.category' && request()->route('categorySlug') === $translation->slug;
                            }
                        }
                    }
                    break;
                case 'cms_page':
                    if (!empty($item['cms_page_id'])) {
                        $cmsPage = \Happytodev\Blogr\Models\CmsPage::find($item['cms_page_id']);
                        if ($cmsPage) {
                            $translation = $cmsPage->translations()->where('locale', $locale)->first();
                            if ($translation) {
                                $url = route('cms.page.show', ['locale' => $locale, 'slug' => $translation->slug]);
                                $isActive = request()->route()->getName() === 'cms.page.show' && request()->route('slug') === $translation->slug;
                            }
                        }
                    }
                    break;
                case 'megamenu':
                    $url = '#'; // Mega menu is a dropdown, no direct URL
                    break;
            }
            
            return ['url' => $url, 'isActive' => $isActive];
        }
    }
@endphp

<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 {{ config('blogr.ui.navigation.sticky', true) ? 'sticky top-0 z-50' : '' }} transition-colors duration-200" 
     x-data="{ mobileMenuOpen: false, openMegaMenu: null }">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo / Site Name -->
            @if(config('blogr.ui.navigation.show_logo', true))
            <div class="flex-shrink-0">
                @php
                    // Determine homepage URL based on configuration and locales
                    $localesEnabled = config('blogr.locales.enabled', false);
                    $homepageType = config('blogr.homepage.type', 'blog');
                    
                    if ($localesEnabled) {
                        // With locales: always include locale in URL
                        if ($homepageType === 'cms') {
                            $homepageUrl = url('/' . $currentLocale);
                        } else {
                            $homepageUrl = route('blog.index', ['locale' => $currentLocale]);
                        }
                    } else {
                        // Without locales: simple URL without locale parameter
                        if ($homepageType === 'cms') {
                            $homepageUrl = url('/');
                        } else {
                            // Check if blog is at root or has a prefix
                            $blogPrefix = config('blogr.route.prefix', '');
                            $blogIsHomepage = config('blogr.route.homepage', false);
                            if ($blogIsHomepage || empty($blogPrefix) || $blogPrefix === '/') {
                                $homepageUrl = url('/');
                            } else {
                                $homepageUrl = url('/' . trim($blogPrefix, '/'));
                            }
                        }
                    }
                    
                    $logo = config('blogr.ui.navigation.logo');
                    $logoDisplay = config('blogr.ui.navigation.logo_display', 'text');
                    $siteName = \Happytodev\Blogr\Helpers\ConfigHelper::getSeoSiteName($currentLocale);
                @endphp
                <a href="{{ $homepageUrl }}" class="flex items-center space-x-3 text-2xl font-bold text-gray-900 dark:text-white hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors">
                    @if($logoDisplay === 'image' || $logoDisplay === 'both')
                        @if($logo)
                            <img src="{{ asset('storage/' . $logo) }}" alt="{{ $siteName }}" class="h-10 w-auto">
                        @endif
                    @endif
                    @if($logoDisplay === 'text' || $logoDisplay === 'both' || !$logo)
                        <span>{{ $siteName }}</span>
                    @endif
                </a>
            </div>
            @endif

            <!-- Navigation Menu Items (Desktop) -->
            @php
                $menuItems = config('blogr.ui.navigation.menu_items', []);
                
                // Auto-add blog link if CMS is homepage and option is enabled
                if (config('blogr.homepage.type') === 'cms' && config('blogr.ui.navigation.auto_add_blog', false)) {
                    // Check if blog link doesn't already exist
                    $hasBlogLink = false;
                    foreach ($menuItems as $item) {
                        if (($item['type'] ?? '') === 'blog') {
                            $hasBlogLink = true;
                            break;
                        }
                    }
                    
                    // Add blog link with multilingual labels if not exists
                    if (!$hasBlogLink) {
                        $blogLabels = [];
                        foreach ($availableLocales as $locale) {
                            $blogLabels[] = [
                                'locale' => $locale,
                                'label' => __('blogr::navigation.blog', [], $locale),
                            ];
                        }
                        
                        array_unshift($menuItems, [
                            'labels' => $blogLabels,
                            'type' => 'blog',
                            'target' => '_self',
                        ]);
                    }
                }
            @endphp
            @if(!empty($menuItems))
            <div class="hidden md:flex items-center space-x-1 flex-1 justify-center">
                @foreach($menuItems as $itemKey => $item)
                    @php
                        $label = getMenuLabel($item, $currentLocale);
                        $urlData = getMenuUrl($item, $currentLocale);
                        $url = $urlData['url'];
                        $isActive = $urlData['isActive'];
                        $target = $item['target'] ?? '_self';
                        $icon = $item['icon'] ?? null;
                        $isMegaMenu = ($item['type'] ?? 'external') === 'megamenu';
                    @endphp
                    
                    @if($isMegaMenu)
                        <!-- Mega Menu Dropdown -->
                        <div class="relative" 
                             x-data="{ open: false }"
                             @mouseenter="open = true" 
                             @mouseleave="open = false">
                            <button class="flex items-center space-x-1 px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <span>{{ $label }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute left-0 mt-2 w-64 rounded-lg shadow-xl bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 py-2 z-50"
                                 style="display: none;">
                                @if(isset($item['children']) && is_array($item['children']))
                                    @foreach($item['children'] as $child)
                                        @php
                                            $childLabel = getMenuLabel($child, $currentLocale);
                                            $childUrlData = getMenuUrl($child, $currentLocale);
                                            $childUrl = $childUrlData['url'];
                                            $childIsActive = $childUrlData['isActive'];
                                            $childTarget = $child['target'] ?? '_self';
                                        @endphp
                                        <a href="{{ $childUrl }}" 
                                           target="{{ $childTarget }}"
                                           class="block px-4 py-2 text-sm {{ $childIsActive ? 'text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] bg-gray-100 dark:bg-gray-700' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors">
                                            {{ $childLabel }}
                                            @if($childTarget === '_blank')
                                                <svg class="inline-block w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Regular Menu Item -->
                        <a href="{{ $url }}" 
                           target="{{ $target }}"
                           class="flex items-center space-x-1 px-4 py-2 rounded-md text-sm font-medium transition-colors
                                  {{ $isActive 
                                      ? 'text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] bg-gray-100 dark:bg-gray-800' 
                                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)]' 
                                  }}">
                            <span>{{ $label }}</span>
                            @if($target === '_blank')
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
            @endif

            <!-- Right Side: Mobile Menu Toggle, Language Switcher & Theme Switcher -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle Button -->
                @if(!empty($menuItems))
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="md:hidden p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                @endif
                
                <!-- Language Switcher -->
                @if(config('blogr.ui.navigation.show_language_switcher', true) && config('blogr.locales.enabled', false) && count($availableLocales) > 1)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" 
                            class="flex items-center space-x-1 px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span class="uppercase">{{ $currentLocale }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5"
                         style="display: none;">
                        <div class="py-1" role="menu">
                            @foreach($availableLocales as $locale)
                            @php
                                $currentRouteName = request()->route()->getName();
                                $currentParams = request()->route()->parameters();
                                
                                // For CMS pages, get the translated slug if it exists
                                if ($currentRouteName === 'cms.page.show' && $cmsPageId) {
                                    // Get the translated slug for this page in the target locale
                                    $cmsTranslation = \Happytodev\Blogr\Models\CmsPageTranslation::where('cms_page_id', $cmsPageId)
                                        ->where('locale', $locale)
                                        ->first();
                                    
                                    if ($cmsTranslation) {
                                        $currentParams['slug'] = $cmsTranslation->slug;
                                    }
                                }
                                
                                $currentParams['locale'] = $locale;
                            @endphp
                            <a href="{{ route($currentRouteName, $currentParams) }}" 
                               class="block px-4 py-2 text-sm {{ $locale === $currentLocale ? 'bg-gray-100 dark:bg-gray-700 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors"
                               role="menuitem">
                                <span class="uppercase font-semibold">{{ $locale }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Theme Switcher -->
                @if(config('blogr.ui.navigation.show_theme_switcher', true))
                <div x-data="themeSwitch()" class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                    <button @click="setTheme('light')" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow': theme === 'light' }"
                            class="p-2 rounded-md transition-all duration-200"
                            title="Light mode">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button @click="setTheme('auto')" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow': theme === 'auto' }"
                            class="p-2 rounded-md transition-all duration-200"
                            title="Auto mode">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button @click="setTheme('dark')" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow': theme === 'dark' }"
                            class="p-2 rounded-md transition-all duration-200"
                            title="Dark mode">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        @if(!empty($menuItems))
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="md:hidden border-t border-gray-200 dark:border-gray-700"
             style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @foreach($menuItems as $itemKey => $item)
                    @php
                        $label = getMenuLabel($item, $currentLocale);
                        $urlData = getMenuUrl($item, $currentLocale);
                        $url = $urlData['url'];
                        $isActive = $urlData['isActive'];
                        $target = $item['target'] ?? '_self';
                        $isMegaMenu = ($item['type'] ?? 'external') === 'megamenu';
                    @endphp
                    
                    @if($isMegaMenu)
                        <!-- Mobile Mega Menu Accordion -->
                        <div x-data="{ open: false }" class="space-y-1">
                            <button @click="open = !open" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <span>{{ $label }}</span>
                                <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition
                                 class="pl-4 space-y-1"
                                 style="display: none;">
                                @if(isset($item['children']) && is_array($item['children']))
                                    @foreach($item['children'] as $child)
                                        @php
                                            $childLabel = getMenuLabel($child, $currentLocale);
                                            $childUrlData = getMenuUrl($child, $currentLocale);
                                            $childUrl = $childUrlData['url'];
                                            $childIsActive = $childUrlData['isActive'];
                                            $childTarget = $child['target'] ?? '_self';
                                        @endphp
                                        <a href="{{ $childUrl }}" 
                                           target="{{ $childTarget }}"
                                           class="block px-3 py-2 rounded-md text-sm {{ $childIsActive ? 'text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] bg-gray-100 dark:bg-gray-700' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }} transition-colors">
                                            {{ $childLabel }}
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Mobile Regular Menu Item -->
                        <a href="{{ $url }}" 
                           target="{{ $target }}"
                           class="flex items-center space-x-2 px-3 py-2 rounded-md text-base font-medium transition-colors
                                  {{ $isActive 
                                      ? 'text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] bg-gray-100 dark:bg-gray-800' 
                                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)]' 
                                  }}">
                            <span>{{ $label }}</span>
                            @if($target === '_blank')
                                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('themeSwitch', () => ({
            theme: localStorage.getItem('theme') || '{{ config('blogr.ui.theme.default', 'light') }}',
            
            init() {
                this.applyTheme();
                
                if (this.theme === 'auto') {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                        if (this.theme === 'auto') {
                            this.applyTheme();
                        }
                    });
                }
            },
            
            setTheme(newTheme) {
                this.theme = newTheme;
                localStorage.setItem('theme', newTheme);
                this.applyTheme();
            },
            
            applyTheme() {
                if (this.theme === 'dark' || (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }));
    });
</script>
@endpush
