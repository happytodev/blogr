<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ config('blogr.ui.theme.default', 'light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    @php
        $fontFamily = config('blogr.ui.theme.font_family', 'Instrument Sans');
        $fontCustomName = config('blogr.ui.theme.font_custom_name');
        $fontCustomUrl = config('blogr.ui.theme.font_custom_url');

        $hbg = config('blogr.ui.theme.header_bg');
        $hbgDark = config('blogr.ui.theme.header_bg_dark');
        $htxt = config('blogr.ui.theme.header_text');
        $htxtDark = config('blogr.ui.theme.header_text_dark');
        $fbg = config('blogr.ui.theme.footer_bg');
        $fbgDark = config('blogr.ui.theme.footer_bg_dark');
        $ftxt = config('blogr.ui.theme.footer_text');
        $ftxtDark = config('blogr.ui.theme.footer_text_dark');
    @endphp

    @if($fontFamily && $fontFamily !== 'custom' && $fontFamily !== 'system')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($fontFamily) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif

    @if($fontCustomUrl && $fontCustomName)
    <style>
        @font-face {
            font-family: '{{ $fontCustomName }}';
            src: url('{{ Storage::url($fontCustomUrl) }}') format('woff2');
            font-weight: 400 700;
            font-display: swap;
        }
    </style>
    @endif

    <!-- Color System CSS Variables -->
    <style>
        :root {
            --font-family: @if($fontFamily === 'custom' && $fontCustomName)'{{ $fontCustomName }}' @elseif($fontFamily && $fontFamily !== 'custom' && $fontFamily !== 'system')'{{ $fontFamily }}' @else'Instrument Sans' @endif;

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
            
            /* Brand Colors — background, buttons, text, highlights */
            --color-bg: {{ config('blogr.ui.theme.bg_color', '#ffffff') }};
            --color-bg-dark: {{ config('blogr.ui.theme.bg_color_dark', '#111827') }};
            --color-btn: {{ config('blogr.ui.theme.btn_color', '#c20be5') }};
            --color-btn-dark: {{ config('blogr.ui.theme.btn_color_dark', '#e166fa') }};
            --color-text: {{ config('blogr.ui.theme.text_color', '#1f2937') }};
            --color-text-dark: {{ config('blogr.ui.theme.text_color_dark', '#f3f4f6') }};
            --color-highlight: {{ config('blogr.ui.theme.highlight_color', '#c20be5') }};
            --color-highlight-dark: {{ config('blogr.ui.theme.highlight_color_dark', '#e166fa') }};
            
            /* Testimonial card colors */
            --color-testimonial-bg: {{ config('blogr.ui.theme.testimonial_bg', '#ffffff') }};
            --color-testimonial-bg-dark: {{ config('blogr.ui.theme.testimonial_bg_dark', '#1f2937') }};
            --color-testimonial-text: {{ config('blogr.ui.theme.testimonial_text', '#374151') }};
            --color-testimonial-text-dark: {{ config('blogr.ui.theme.testimonial_text_dark', '#d1d5db') }};

            /* Header & Footer — user-set color, or auto from bg */
            --color-header-bg: {{ $hbg ?: 'var(--color-bg)' }};
            --color-header-bg-dark: {{ $hbgDark ?: 'var(--color-bg-dark)' }};
            --color-header-text: {{ $htxt ?: 'var(--color-text)' }};
            --color-header-text-dark: {{ $htxtDark ?: 'var(--color-text-dark)' }};
            --color-footer-bg: {{ $fbg ?: 'var(--color-bg)' }};
            --color-footer-bg-dark: {{ $fbgDark ?: 'var(--color-bg-dark)' }};
            --color-footer-text: {{ $ftxt ?: 'var(--color-text)' }};
            --color-footer-text-dark: {{ $ftxtDark ?: 'var(--color-text-dark)' }};
            
            /* Prose (markdown) — align with brand colors */
            --tw-prose-body: var(--color-text);
            --tw-prose-headings: var(--color-text);
            --tw-prose-links: var(--color-highlight);
            --tw-prose-bold: var(--color-text);
            --tw-prose-code: var(--color-text);
            --tw-prose-quotes: var(--color-text);
            --tw-prose-quote-borders: var(--color-highlight);
            --tw-prose-hr: var(--color-text);
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            transition: background-color 0.3s, color 0.3s;
        }
        .dark body {
            background-color: var(--color-bg-dark);
            color: var(--color-text-dark);
        }

        .testimonial-card {
            background-color: var(--color-testimonial-bg);
            color: var(--color-testimonial-text);
        }
        .dark .testimonial-card {
            background-color: var(--color-testimonial-bg-dark);
            color: var(--color-testimonial-text-dark);
        }

        .dark .prose,
        .dark .prose-lg,
        .dark .prose-sm,
        .dark .prose-xl {
            --tw-prose-body: var(--color-text-dark);
            --tw-prose-headings: var(--color-text-dark);
            --tw-prose-links: var(--color-highlight-dark);
            --tw-prose-bold: var(--color-text-dark);
            --tw-prose-code: var(--color-text-dark);
            --tw-prose-quotes: var(--color-text-dark);
            --tw-prose-quote-borders: var(--color-highlight-dark);
            --tw-prose-hr: var(--color-text-dark);
        }
    </style>

    <!-- Structured Data (JSON-LD) -->
    @if (config('blogr.seo.structured_data.enabled', true) && isset($seoData))
        <script type="application/ld+json">
            {!! \Happytodev\Blogr\Helpers\SEOHelper::generateJsonLd($seoData) !!}
        </script>
    @endif

    {{-- Analytics Tracking --}}
    @stack('analytics-before')
    @include('blogr::components.analytics-tracker')
    @stack('analytics-after')

    {{-- RSS Auto-discovery --}}
    @if(config('blogr.rss.enabled', true))
    @php
        $rssLocaleEnabled = config('blogr.locales.enabled', false);
        $mainFeedUrl = $rssLocaleEnabled
            ? route('blog.feed', ['locale' => $currentLocale])
            : route('blog.feed');
    @endphp
    <link rel="alternate" type="application/rss+xml" title="{{ config('app.name', 'Blog') }} - RSS Feed" href="{{ $mainFeedUrl }}">
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

<body class="text-gray-900 dark:text-gray-100 transition-colors duration-200">
    @stack('body-start')

    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        @if(config('blogr.ui.navigation.enabled', true))
            @include('blogr::components.navigation', [
                'currentLocale' => $currentLocale ?? config('blogr.locales.default', 'en'),
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
    @stack('cookie-consent')
    {{-- Back to top button (simple component) --}}
    <!-- DEBUG: BEFORE INCLUDE -->
    @include('blogr::components.back-to-top')
    <!-- DEBUG: AFTER INCLUDE -->
    @stack('scripts')
</body>

</html>
