<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ config('blogr.ui.theme.default', 'light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('seo-data')

    <!-- Primary Meta Tags -->
    <title>{{ $seoData['title'] ?? config('blogr.seo.default_title', 'Blog') }}</title>
    <meta name="title" content="{{ $seoData['title'] ?? config('blogr.seo.default_title', 'Blog') }}">
    <meta name="description" content="{{ $seoData['description'] ?? config('blogr.seo.default_description', 'Discover our latest articles and insights') }}">
    <meta name="keywords" content="{{ $seoData['keywords'] ?? config('blogr.seo.default_keywords', 'blog, articles, news, insights') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoData['canonical'] ?? url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoData['og_type'] ?? config('blogr.seo.og.type', 'website') }}">
    <meta property="og:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="og:title" content="{{ $seoData['title'] ?? config('blogr.seo.default_title', 'Blog') }}">
    <meta property="og:description" content="{{ $seoData['description'] ?? config('blogr.seo.default_description', 'Discover our latest articles and insights') }}">
    <meta property="og:image" content="{{ $seoData['image'] ?? asset(config('blogr.seo.og.image', '/images/blogr.webp')) }}">
    <meta property="og:image:width" content="{{ $seoData['image_width'] ?? config('blogr.seo.og.image_width', 1200) }}">
    <meta property="og:image:height" content="{{ $seoData['image_height'] ?? config('blogr.seo.og.image_height', 630) }}">
    <meta property="og:site_name" content="{{ config('blogr.seo.site_name', 'My Blog') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="twitter:title" content="{{ $seoData['title'] ?? config('blogr.seo.default_title', 'Blog') }}">
    <meta property="twitter:description"
        content="{{ $seoData['description'] ?? config('blogr.seo.default_description', 'Discover our latest articles and insights') }}">
    <meta property="twitter:image" content="{{ $seoData['image'] ?? asset(config('blogr.seo.og.image', '/images/blogr.webp')) }}">
    @if (config('blogr.seo.twitter_handle'))
        <meta property="twitter:site" content="{{ config('blogr.seo.twitter_handle') }}">
        <meta property="twitter:creator" content="{{ config('blogr.seo.twitter_handle') }}">
    @endif

    <!-- Additional Meta Tags -->
    <meta name="robots" content="{{ $seoData['robots'] ?? 'index, follow' }}">
    <meta name="author" content="{{ $seoData['author'] ?? config('blogr.seo.site_name', 'My Blog') }}">

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
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Structured Data (JSON-LD) -->
    @if (config('blogr.seo.structured_data.enabled', true))
        <script type="application/ld+json">
            {!! \Happytodev\Blogr\Helpers\SEOHelper::generateJsonLd($seoData) !!}
        </script>
    @endif

    @stack('head')
    
    <!-- Temporary dark mode styles until CSS is recompiled -->
    <style>
        .dark body, .dark .bg-gray-50 { background-color: rgb(17 24 39); }
        .dark .bg-white { background-color: rgb(31 41 55); }
        .dark .bg-gray-900 { background-color: rgb(17 24 39); }
        .dark .bg-gray-800 { background-color: rgb(31 41 55); }
        .dark .bg-gray-700 { background-color: rgb(55 65 81); }
        .dark .text-gray-900 { color: rgb(243 244 246); }
        .dark .text-gray-800 { color: rgb(229 231 235); }
        .dark .text-gray-700 { color: rgb(209 213 219); }
        .dark .text-gray-600 { color: rgb(156 163 175); }
        .dark .text-gray-500 { color: rgb(107 114 128); }
        .dark .text-gray-400 { color: rgb(156 163 175); }
        .dark .text-gray-300 { color: rgb(209 213 219); }
        .dark .text-gray-100 { color: rgb(243 244 246); }
        .dark .text-white { color: rgb(255 255 255); }
        .dark .border-gray-200 { border-color: rgb(55 65 81); }
        .dark .border-gray-700 { border-color: rgb(55 65 81); }
        .dark .hover\:bg-gray-100:hover { background-color: rgb(55 65 81); }
        .dark .hover\:bg-gray-800:hover { background-color: rgb(55 65 81); }
        .dark .hover\:bg-gray-700:hover { background-color: rgb(75 85 99); }
    </style>
    
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
                'availableLocales' => $availableLocales ?? config('blogr.locales.available', ['en'])
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
    @stack('scripts')
</body>

</html>
