<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ config('blogr.ui.theme.default', 'light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('seo-data')

    @php
        $currentLocale = app()->getLocale();
    @endphp

    <!-- Primary Meta Tags -->
    <title>{{ $seoData['title'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultTitle($currentLocale) }}</title>
    <meta name="title" content="{{ $seoData['title'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultTitle($currentLocale) }}">
    <meta name="description" content="{{ $seoData['description'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultDescription($currentLocale) }}">
    <meta name="keywords" content="{{ $seoData['keywords'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultKeywords($currentLocale) }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoData['canonical'] ?? url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoData['og_type'] ?? config('blogr.seo.og.type', 'website') }}">
    <meta property="og:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="og:title" content="{{ $seoData['title'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultTitle($currentLocale) }}">
    <meta property="og:description" content="{{ $seoData['description'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultDescription($currentLocale) }}">
    <meta property="og:image" content="{{ $seoData['image'] ?? asset(config('blogr.seo.og.image', '/images/blogr.webp')) }}">
    <meta property="og:image:width" content="{{ $seoData['image_width'] ?? config('blogr.seo.og.image_width', 1200) }}">
    <meta property="og:image:height" content="{{ $seoData['image_height'] ?? config('blogr.seo.og.image_height', 630) }}">
    <meta property="og:site_name" content="{{ \Happytodev\Blogr\Helpers\ConfigHelper::getSeoSiteName($currentLocale) }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="twitter:title" content="{{ $seoData['title'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultTitle($currentLocale) }}">
    <meta property="twitter:description"
        content="{{ $seoData['description'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultDescription($currentLocale) }}">
    <meta property="twitter:image" content="{{ $seoData['image'] ?? asset(config('blogr.seo.og.image', '/images/blogr.webp')) }}">
    @if (config('blogr.seo.twitter_handle'))
        <meta property="twitter:site" content="{{ config('blogr.seo.twitter_handle') }}">
        <meta property="twitter:creator" content="{{ config('blogr.seo.twitter_handle') }}">
    @endif

    <!-- Additional Meta Tags -->
    <meta name="robots" content="{{ $seoData['robots'] ?? 'index, follow' }}">
    <meta name="author" content="{{ $seoData['author'] ?? \Happytodev\Blogr\Helpers\ConfigHelper::getSeoSiteName($currentLocale) }}">

    @if (isset($seoData['published_time']))
        <meta property="article:published_time" content="{{ $seoData['published_time'] }}">
    @endif

    @if (isset($seoData['modified_time']))
        <meta property="article:modified_time" content="{{ $seoData['modified_time'] }}">
    @endif

    @if (isset($seoData['tags']) && is_array($seoData['tags']))
        @foreach ($seoData['tags'] as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Color System CSS Variables -->
    <style>
        :root {
            /* Primary Colors */
            --color-primary: {{ config('blogr.ui.theme.primary_color', '#c20be5') }};
            --color-primary-dark: {{ config('blogr.ui.theme.primary_color_dark', '#9b0ab8') }};
            --color-primary-hover: {{ config('blogr.ui.theme.primary_color_hover', '#d946ef') }};
            --color-primary-hover-dark: {{ config('blogr.ui.theme.primary_color_hover_dark', '#a855f7') }};
            
            /* Card Colors */
            --color-blog-card-bg: {{ config('blogr.ui.appearance.blog_card_bg', '#ffffff') }};
            --color-blog-card-bg-dark: {{ config('blogr.ui.appearance.blog_card_bg_dark', '#1f2937') }};
            --color-series-card-bg: {{ config('blogr.ui.appearance.series_card_bg', '#f9fafb') }};
            --color-series-card-bg-dark: {{ config('blogr.ui.appearance.series_card_bg_dark', '#374151') }};
            
            /* Category Colors */
            --color-category-bg: {{ config('blogr.ui.theme.category_bg', '#e0f2fe') }};
            --color-category-bg-dark: {{ config('blogr.ui.theme.category_bg_dark', '#0c4a6e') }};
            
            /* Tag Colors */
            --color-tag-bg: {{ config('blogr.ui.theme.tag_bg', '#d1fae5') }};
            --color-tag-bg-dark: {{ config('blogr.ui.theme.tag_bg_dark', '#065f46') }};
            
            /* Author Colors */
            --color-author-bg: {{ config('blogr.ui.theme.author_bg', '#fef3c7') }};
            --color-author-bg-dark: {{ config('blogr.ui.theme.author_bg_dark', '#78350f') }};
        }
    </style>

    <!-- Structured Data (JSON-LD) -->
    @if (config('blogr.seo.structured_data.enabled', true) && isset($seoData))
        <script type="application/ld+json">
            {!! \Happytodev\Blogr\Helpers\SEOHelper::generateJsonLd($seoData) !!}
        </script>
    @endif

    @stack('head')
    @stack('styles')
    
    <!-- Dark mode initialization script (runs before page render to prevent flash) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || '{{ config('blogr.ui.theme.default', 'light') }}';
            if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    @stack('body-start')

    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        @if(config('blogr.ui.navigation.enabled', true))
            @include('blogr::components.navigation', [
                'currentLocale' => $currentLocale ?? config('blogr.locales.default', 'en'),
                'availableLocales' => $availableLocales ?? config('blogr.locales.available', ['en']),
                'cmsPageId' => $page?->id ?? null,
            ])
        @endif

        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <!-- Footer -->
        @include('blogr::components.footer')
    </div>

    @stack('body-end')
    {{-- Back to top button (simple component) --}}
    <!-- DEBUG: BEFORE INCLUDE -->
    @include('blogr::components.back-to-top')
    <!-- DEBUG: AFTER INCLUDE -->
    @stack('scripts')
</body>

</html>
