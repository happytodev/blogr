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
    @php
        $faviconPath = config('blogr.ui.favicon.path');
        $defaultFavicon = asset('vendor/blogr/images/blogr-favicon.svg');
    @endphp
    @if($faviconPath)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconPath) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $faviconPath) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ $defaultFavicon }}">
    @endif

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $fontFamily = config('blogr.ui.theme.font_family', 'Instrument Sans');
        $fontCustomName = config('blogr.ui.theme.font_custom_name');
        $fontCustomUrl = config('blogr.ui.theme.font_custom_url');
        $fontCustomGoogle = config('blogr.ui.theme.font_custom_google');

        // Resolve actual font name for display and Google Fonts URL
        $resolvedFont = match (true) {
            $fontFamily === 'custom' && $fontCustomGoogle => $fontCustomGoogle,
            $fontFamily === 'custom' && $fontCustomName => $fontCustomName,
            $fontFamily === 'custom' => 'Instrument Sans',
            $fontFamily === 'system' => null,
            default => $fontFamily,
        };

        // Strip axis parameters (e.g. :ital@1) for CSS font-family usage
        $fontFamilyCss = $resolvedFont ? explode(':', $resolvedFont)[0] : null;

        $fontSizeBase = config('blogr.ui.theme.font_size_base', 16);

        $hbg = config('blogr.ui.theme.header_bg');
        $hbgDark = config('blogr.ui.theme.header_bg_dark');
        $htxt = config('blogr.ui.theme.header_text');
        $htxtDark = config('blogr.ui.theme.header_text_dark');
        $fbg = config('blogr.ui.theme.footer_bg');
        $fbgDark = config('blogr.ui.theme.footer_bg_dark');
        $ftxt = config('blogr.ui.theme.footer_text');
        $ftxtDark = config('blogr.ui.theme.footer_text_dark');

        // Apply brightness sliders (-10 to +10) to header/footer bg
        $hBright = (int) config('blogr.ui.theme.header_brightness', 0);
        $hBrightDark = (int) config('blogr.ui.theme.header_brightness_dark', 0);
        $fBright = (int) config('blogr.ui.theme.footer_brightness', 0);
        $fBrightDark = (int) config('blogr.ui.theme.footer_brightness_dark', 0);

        if ($hBright !== 0) {
            $base = $hbg ?: config('blogr.ui.theme.bg_color', '#ffffff');
            $hbg = \Happytodev\Blogr\Helpers\ColorHelper::adjustBrightness($base, $hBright);
        }
        if ($hBrightDark !== 0) {
            $base = $hbgDark ?: config('blogr.ui.theme.bg_color_dark', '#111827');
            $hbgDark = \Happytodev\Blogr\Helpers\ColorHelper::adjustBrightness($base, $hBrightDark);
        }
        if ($fBright !== 0) {
            $base = $fbg ?: config('blogr.ui.theme.bg_color', '#ffffff');
            $fbg = \Happytodev\Blogr\Helpers\ColorHelper::adjustBrightness($base, $fBright);
        }
        if ($fBrightDark !== 0) {
            $base = $fbgDark ?: config('blogr.ui.theme.bg_color_dark', '#111827');
            $fbgDark = \Happytodev\Blogr\Helpers\ColorHelper::adjustBrightness($base, $fBrightDark);
        }
    @endphp

    @if($fontFamily === 'custom' && $fontCustomGoogle)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $fontCustomGoogle) }}&display=swap" rel="stylesheet">
    @elseif($resolvedFont)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($resolvedFont) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --font-family: '{{ $fontFamilyCss ?? 'Instrument Sans' }}';
            --font-size-base: {{ $fontSizeBase }};

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
            --color-btn: {{ config('blogr.ui.theme.btn_color', '#9b0ab8') }};
            --color-btn-dark: {{ config('blogr.ui.theme.btn_color_dark', '#d946ef') }};
            --color-text: {{ config('blogr.ui.theme.text_color', '#1f2937') }};
            --color-text-dark: {{ config('blogr.ui.theme.text_color_dark', '#f3f4f6') }};
            --color-highlight: {{ config('blogr.ui.theme.highlight_color', '#c20be5') }};
            --color-highlight-dark: {{ config('blogr.ui.theme.highlight_color_dark', '#e166fa') }};
            
            /* Testimonial card colors */
            --color-testimonial-bg: {{ config('blogr.ui.theme.testimonial_bg', '#ffffff') }};
            --color-testimonial-bg-dark: {{ config('blogr.ui.theme.testimonial_bg_dark', '#1f2937') }};
            --color-testimonial-text: {{ config('blogr.ui.theme.testimonial_text', '#374151') }};
            --color-testimonial-text-dark: {{ config('blogr.ui.theme.testimonial_text_dark', '#d1d5db') }};

            /* Header & Footer — raw user values (only set if user provided) */
            @if($hbg)--_header-bg: {{ $hbg }};@endif
            @if($hbgDark)--_header-bg-dark: {{ $hbgDark }};@endif
            @if($htxt)--_header-text: {{ $htxt }};@endif
            @if($htxtDark)--_header-text-dark: {{ $htxtDark }};@endif
            @if($fbg)--_footer-bg: {{ $fbg }};@endif
            @if($fbgDark)--_footer-bg-dark: {{ $fbgDark }};@endif
            @if($ftxt)--_footer-text: {{ $ftxt }};@endif
            @if($ftxtDark)--_footer-text-dark: {{ $ftxtDark }};@endif

            /* Computed header/footer colors — switch with .dark */
            --color-header-bg: var(--_header-bg, var(--color-bg));
            --color-header-text: var(--_header-text, var(--color-text));
            --color-footer-bg: var(--_footer-bg, var(--color-bg));
            --color-footer-text: var(--_footer-text, var(--color-text));
            
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

        html {
            font-size: clamp({{ max($fontSizeBase - 2, 12) }}px, calc({{ $fontSizeBase }}px + 0.3vw), {{ min($fontSizeBase + 4, 28) }}px);
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            font-family: var(--font-family), ui-sans-serif, system-ui, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }
        .dark body {
            background-color: var(--color-bg-dark);
            color: var(--color-text-dark);
            font-family: var(--font-family), ui-sans-serif, system-ui, sans-serif;
        }

        main {
            background-color: var(--color-bg);
            transition: background-color 0.3s, color 0.3s;
        }
        .dark main {
            background-color: var(--color-bg-dark);
        }

        .dark {
            --color-header-bg: var(--_header-bg-dark, var(--color-bg-dark));
            --color-header-text: var(--_header-text-dark, var(--color-text-dark));
            --color-footer-bg: var(--_footer-bg-dark, var(--color-bg-dark));
            --color-footer-text: var(--_footer-text-dark, var(--color-text-dark));
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

    <style>
        /* Shiki Syntax Highlighting */
        .prose .shiki { position: relative; padding-top: 2.5rem; border-radius: 0.5rem; overflow-x: auto; margin-top: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1); }
        .prose .shiki code { font-size: 0.875rem; line-height: 1.625; }
        .prose .shiki[data-language]::before { content: attr(data-language); position: absolute; top: 0; left: 0; right: 0; padding: 0.25rem 1rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.025em; text-transform: uppercase; color: #6b7280; background-color: rgba(0,0,0,0.05); border-bottom: 1px solid rgba(0,0,0,0.08); border-radius: 0.5rem 0.5rem 0 0; pointer-events: none; }
        .dark .prose .shiki[data-language]::before { color: #9ca3af; background-color: rgba(255,255,255,0.05); border-bottom-color: rgba(255,255,255,0.08); }
        .shiki[data-line-numbers] code { counter-reset: shiki-line; }
        .shiki[data-line-numbers] .line::before { counter-increment: shiki-line; content: counter(shiki-line); display: inline-block; width: 2rem; margin-right: 1rem; text-align: right; color: #6b7280; font-size: 0.75rem; user-select: none; opacity: 0.5; }
        .dark .shiki[data-line-numbers] .line::before { color: #9ca3af; }
        .dark .shiki { background-color: var(--shiki-dark-bg) !important; }
        .dark .shiki code { background-color: transparent !important; color: var(--shiki-dark) !important; }
        .dark .shiki span { color: var(--shiki-dark) !important; }
        .shiki-fallback { border-radius: 0.5rem; overflow-x: auto; margin-top: 1.5rem; margin-bottom: 1.5rem; background-color: #1f2937; color: #f3f4f6; padding: 1rem; font-size: 0.875rem; }
        .shiki-fallback[data-language]::before { content: attr(data-language); position: absolute; top: 0; left: 0; right: 0; padding: 0.25rem 1rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #9ca3af; background-color: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.08); border-radius: 0.5rem 0.5rem 0 0; pointer-events: none; }
        .copy-button { position: absolute; top: 0.25rem; right: 0.5rem; z-index: 10; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; background: transparent; border: 1px solid transparent; border-radius: 0.25rem; cursor: pointer; opacity: 0; transition: opacity 0.2s, color 0.2s, background 0.2s, border-color 0.2s; }
        .shiki:hover .copy-button, .shiki-fallback:hover .copy-button { opacity: 1; }
        .copy-button:hover { color: #374151; background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.1); }
        .dark .copy-button { color: #9ca3af; }
        .dark .copy-button:hover { color: #d1d5db; background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.12); }
        .copy-button.copied { color: #059669 !important; border-color: #059669 !important; }
        .dark .copy-button.copied { color: #34d399 !important; border-color: #34d399 !important; }

        /* Callout / admonition blocks */
        .docs-callout { border-radius: 0.5rem; padding: 1rem 1.25rem; margin: 1.5rem 0; border-left: 4px solid; }
        .docs-callout--tip { border-color: #10b981; background-color: #ecfdf5; }
        .dark .docs-callout--tip { background-color: rgba(16,185,129,0.1); }
        .docs-callout--info { border-color: #3b82f6; background-color: #eff6ff; }
        .dark .docs-callout--info { background-color: rgba(59,130,246,0.1); }
        .docs-callout--danger { border-color: #ef4444; background-color: #fef2f2; }
        .dark .docs-callout--danger { background-color: rgba(239,68,68,0.1); }
        .docs-callout--caution { border-color: #f59e0b; background-color: #fffbeb; }
        .dark .docs-callout--caution { background-color: rgba(245,158,11,0.1); }
        .docs-callout__title { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 1.125rem; margin: 0 0 0.5rem; }
        .docs-callout__icon { flex-shrink: 0; width: 32px; height: 32px; }
        .docs-callout--tip .docs-callout__title { color: #065f46; }
        .dark .docs-callout--tip .docs-callout__title { color: #6ee7b7; }
        .docs-callout--info .docs-callout__title { color: #1e40af; }
        .dark .docs-callout--info .docs-callout__title { color: #93c5fd; }
        .docs-callout--danger .docs-callout__title { color: #991b1b; }
        .dark .docs-callout--danger .docs-callout__title { color: #fca5a5; }
        .docs-callout--caution .docs-callout__title { color: #92400e; }
        .dark .docs-callout--caution .docs-callout__title { color: #fcd34d; }
        .docs-callout__title--icon-only { margin-bottom: 0; }
        .docs-callout__content { font-size: 0.75rem; }
        .docs-callout__content > :first-child { margin-top: 0; }
        .docs-callout__content > :last-child { margin-bottom: 0; }
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

<body class="font-sans text-gray-900 dark:text-gray-100 transition-colors duration-200" style="font-family: '{{ $fontFamilyCss ?? 'Instrument Sans' }}', ui-sans-serif, system-ui, sans-serif !important;">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[60] focus:px-4 focus:py-2 focus:bg-white focus:text-gray-900 focus:ring-2 focus:ring-primary-600 focus:rounded-lg focus:outline-none">
        Skip to main content
    </a>
    @stack('body-start')

    @if($preview ?? false)
        <div style="position: fixed; top: 0; left: 0; right: 0; z-index: 9999; background: #ea580c; color: white; text-align: center; padding: 8px 16px; font-size: 14px; font-weight: 600;">
            🔍 PREVIEW — Draft mode &middot; changes not published
        </div>
        <div style="margin-top: 40px;"></div>
    @endif

    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        @if(config('blogr.ui.navigation.enabled', true))
            @include('blogr::components.navigation', [
                'currentLocale' => $currentLocale ?? config('blogr.locales.default', 'en'),
                'cmsPageId' => $page?->id ?? null,
            ])
        @endif

        <!-- Main Content -->
        <main id="main-content" class="flex-grow" style="scroll-margin-top: 5rem;">
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

    <script>
        (function() {
            function addCopyButtons() {
                document.querySelectorAll('.shiki, .shiki-fallback').forEach(function(pre) {
                    if (pre.querySelector('.copy-button')) return;

                    var btn = document.createElement('button');
                    btn.className = 'copy-button';
                    btn.type = 'button';
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Copy';
                    pre.style.position = 'relative';
                    pre.appendChild(btn);

                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var code = pre.querySelector('code');
                        var text = code ? code.textContent : '';
                        var cleaned = text.replace(/\n{3,}$/, '\n');

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(cleaned).then(function() {
                                showCopied(btn);
                            }, function() {
                                fallbackCopy(cleaned, btn);
                            });
                        } else {
                            fallbackCopy(cleaned, btn);
                        }
                    });
                });
            }

            function fallbackCopy(text, btn) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.cssText = 'position:fixed;left:-9999px;top:-9999px;';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try {
                    document.execCommand('copy');
                    showCopied(btn);
                } catch (e) {
                    btn.textContent = 'Failed';
                }
                document.body.removeChild(ta);
            }

            function showCopied(btn) {
                btn.classList.add('copied');
                btn.setAttribute('role', 'status');
                var original = btn.innerHTML;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
                setTimeout(function() {
                    btn.classList.remove('copied');
                    btn.innerHTML = original;
                }, 2000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', addCopyButtons);
            } else {
                addCopyButtons();
            }
        })();
    </script>

    @stack('scripts')
</body>

</html>
