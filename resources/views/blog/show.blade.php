@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@push('styles')
<style>
    /* General link hover style - only underline when hovering the link itself */
    /* Target content links specifically (paragraphs, lists, etc.) */
    .prose p a:hover,
    .prose li a:hover,
    .prose td a:hover,
    .prose blockquote a:hover,
    .prose h1 a:not(.heading-permalink):hover,
    .prose h2 a:not(.heading-permalink):hover,
    .prose h3 a:not(.heading-permalink):hover,
    .prose h4 a:not(.heading-permalink):hover,
    .prose h5 a:not(.heading-permalink):hover,
    .prose h6 a:not(.heading-permalink):hover {
        text-decoration: underline !important;
        color: var(--color-primary-hover) !important;
    }
    
    .dark .prose p a:hover,
    .dark .prose li a:hover,
    .dark .prose td a:hover,
    .dark .prose blockquote a:hover,
    .dark .prose h1 a:not(.heading-permalink):hover,
    .dark .prose h2 a:not(.heading-permalink):hover,
    .dark .prose h3 a:not(.heading-permalink):hover,
    .dark .prose h4 a:not(.heading-permalink):hover,
    .dark .prose h5 a:not(.heading-permalink):hover,
    .dark .prose h6 a:not(.heading-permalink):hover {
        color: var(--color-primary-hover-dark) !important;
    }
    
    /* Table of Contents Styles */
    .toc {
        background-color: rgb(249 250 251);
        padding: 1rem;
        border-radius: 0.5rem;
    }
    
    .dark .toc {
        background-color: rgb(31 41 55 / 0.5);
    }
    
    /* TOC centered (inline with content) */
    .prose .toc.blogr-toc-center {
        border-left: 4px solid rgb(59 130 246);
        border-radius: 0 0.5rem 0.5rem 0;
        margin-bottom: 1.5rem;
    }
    
    .dark .prose .toc.blogr-toc-center {
        border-left-color: rgb(96 165 250);
    }
    
    /* TOC Sidebar - Hidden on mobile, visible on desktop */
    .toc-sidebar-wrapper {
        display: none;
    }
    
    @media (min-width: 768px) {
        .toc-sidebar-wrapper {
            display: block;
        }
    }
    
    .toc.blogr-toc-sidebar {
        position: sticky;
        top: 6rem;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
        border-left: 3px solid rgb(59 130 246);
        border-radius: 0 0.5rem 0.5rem 0;
        font-size: 0.875rem; /* Smaller font size */
        line-height: 1.6; /* Better line spacing */
    }
    
    .dark .toc.blogr-toc-sidebar {
        border-left-color: rgb(96 165 250);
    }
    
    .toc.blogr-toc-right {
        border-left: none;
        border-right: 3px solid rgb(59 130 246);
        border-radius: 0.5rem 0 0 0.5rem;
    }
    
    .dark .toc.blogr-toc-right {
        border-right-color: rgb(96 165 250);
    }
    
    /* Sidebar TOC links styling */
    .toc.blogr-toc-sidebar a {
        display: block;
        padding: 0.375rem 0.5rem;
        margin: 0.125rem 0;
        border-radius: 0.25rem;
        transition: background-color 0.2s, color 0.2s;
    }
    
    .toc.blogr-toc-sidebar a:hover {
        background-color: var(--color-primary);
        color: white !important;
    }
    
    .dark .toc.blogr-toc-sidebar a:hover {
        background-color: var(--color-primary-dark);
        color: white !important;
    }
    
    /* Mobile TOC Container - Sticky wrapper */
    .toc-mobile-container {
        position: sticky;
        top: 4rem; /* Below the navigation bar */
        z-index: 30;
        margin-bottom: 1.5rem;
    }
    
    /* Mobile TOC Button */
    .toc-mobile-button {
        background: rgb(249 250 251);
        border: 1px solid rgb(229 231 235);
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    }
    
    .dark .toc-mobile-button {
        background: rgb(31 41 55);
        border-color: rgb(55 65 81);
    }
    
    @media (min-width: 768px) {
        .toc-mobile-container {
            display: none;
        }
    }
    
    /* Mobile TOC Dropdown */
    .toc-mobile-dropdown {
        max-height: 70vh;
        overflow-y: auto;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    }
    
    /* TOC Collapsible - Collapse entire TOC via title */
    .toc-wrapper-collapsible h1 {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.2s;
        padding: 0.5rem;
        margin: -0.5rem -0.5rem 0.5rem -0.5rem;
        border-radius: 0.375rem;
        position: relative;
    }
    
    .toc-wrapper-collapsible h1:hover {
        background-color: rgb(243 244 246);
    }
    
    .dark .toc-wrapper-collapsible h1:hover {
        background-color: rgb(55 65 81);
    }
    
    /* Pulse animation on hover to indicate clickability */
    .toc-wrapper-collapsible h1:hover .toc-toggle-icon {
        animation: tocPulse 1.5s ease-in-out infinite;
    }
    
    .toc-toggle-icon {
        margin-left: 0.5rem;
        transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .toc-toggle-icon svg {
        width: 100%;
        height: 100%;
        stroke: rgb(107 114 128); /* Gray-500 */
        stroke-width: 2.5;
        fill: none;
    }
    
    .dark .toc-toggle-icon svg {
        stroke: rgb(156 163 175); /* Gray-400 for dark mode */
    }
    
    .toc-toggle-icon.collapsed {
        transform: rotate(-90deg);
    }
    
    /* Bounce animation when toggling */
    .toc-toggle-icon.toggling {
        animation: tocBounce 0.5s ease-out;
    }
    
    /* Keyframes for pulse effect on hover */
    @keyframes tocPulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    /* Keyframes for bounce effect on toggle */
    @keyframes tocBounce {
        0%, 100% {
            transform: translateY(0);
        }
        25% {
            transform: translateY(-4px);
        }
        50% {
            transform: translateY(0);
        }
        75% {
            transform: translateY(-2px);
        }
    }
    
    /* Combined animation for collapsed state with bounce */
    .toc-toggle-icon.collapsed.toggling {
        animation: tocBounceCollapsed 0.5s ease-out;
    }
    
    @keyframes tocBounceCollapsed {
        0%, 100% {
            transform: rotate(-90deg) translateX(0);
        }
        25% {
            transform: rotate(-90deg) translateX(-4px);
        }
        50% {
            transform: rotate(-90deg) translateX(0);
        }
        75% {
            transform: rotate(-90deg) translateX(-2px);
        }
    }
    
    .toc-content-wrapper {
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }
    
    .toc-content-wrapper.collapsed {
        max-height: 0 !important;
        opacity: 0;
        margin-top: 0 !important;
    }
    
    /* Remove bullet points from TOC lists */
    .prose .toc ul,
    .prose .toc ol {
        list-style-type: none;
        padding-left: 0;
        margin-left: 0;
    }
    
    .prose .toc ul ul,
    .prose .toc ol ol {
        padding-left: 1rem;
    }
    
    .prose .toc li {
        list-style-type: none;
    }
    
    .prose .toc li::before {
        content: none;
    }
    
    .prose .toc li::marker {
        content: none;
    }
    
    .prose .toc a {
        text-decoration: none !important;
        color: rgb(55 65 81);
        transition: color 0.2s;
    }
    
    .dark .prose .toc a {
        color: rgb(209 213 219);
    }
    
    .prose .toc a:hover {
        color: var(--color-primary-hover);
        text-decoration: underline !important;
    }
    
    .dark .prose .toc a:hover {
        color: var(--color-primary-hover-dark);
    }
    
    /* Heading Permalink Styles */
    .prose .heading-permalink {
        text-decoration: none !important;
        color: rgb(156 163 175);
        @if(($permalinkConfig['visibility'] ?? 'hover') === 'hover')
        opacity: 0;
        @else
        opacity: 1;
        @endif
        transition: opacity 0.2s, color 0.2s;
        @php
            $spacing = $permalinkConfig['spacing'] ?? 'after';
        @endphp
        @if($spacing === 'before')
        margin-left: 0.5rem;
        @elseif($spacing === 'after')
        margin-right: 0.5rem;
        @elseif($spacing === 'both')
        margin-left: 0.5rem;
        margin-right: 0.5rem;
        @endif
    }
    
    .dark .prose .heading-permalink {
        color: rgb(75 85 99);
    }
    
    @if(($permalinkConfig['visibility'] ?? 'hover') === 'hover')
    .prose h1:hover .heading-permalink,
    .prose h2:hover .heading-permalink,
    .prose h3:hover .heading-permalink,
    .prose h4:hover .heading-permalink,
    .prose h5:hover .heading-permalink,
    .prose h6:hover .heading-permalink {
        opacity: 1;
    }
    @endif
    
    .prose .heading-permalink:hover {
        color: var(--color-primary-hover) !important;
        text-decoration: none !important;
    }
    
    .dark .prose .heading-permalink:hover {
        color: var(--color-primary-hover-dark) !important;
    }
    
    /* Prism.js Code Blocks Styling */
    .prose pre[class*="language-"] {
        @apply rounded-lg shadow-lg my-6;
    }
    
    .prose code[class*="language-"],
    .prose pre[class*="language-"] {
        @apply text-sm;
    }
    
    /* Copy button styling */
    .prose div.code-toolbar > .toolbar {
        @apply opacity-0 transition-opacity duration-200;
    }
    
    .prose div.code-toolbar:hover > .toolbar {
        @apply opacity-100;
    }
</style>

<!-- Prism.js Syntax Highlighting -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" class="prism-theme-light">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" class="prism-theme-dark">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>

<script>
    // Configure Prism autoloader
    Prism.plugins.autoloader.languages_path = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/';
    
    // Add line numbers class to all pre elements
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre[class*="language-"]').forEach(function(pre) {
            pre.classList.add('line-numbers');
        });
        
        // Re-highlight after adding classes
        Prism.highlightAll();
    });
</script>
@endpush

@section('content')
    @php
        $tocPos = $tocPosition ?? 'center';
        $hasSidebarToc = in_array($tocPos, ['left', 'right']) && isset($tocHtml);
    @endphp

    <div class="container mx-auto px-4 py-12 @if($hasSidebarToc) max-w-7xl @else max-w-4xl @endif">
        @if($hasSidebarToc)
            <!-- Mobile TOC Button (sticky at top, only visible on mobile) -->
            <div x-data="{ open: false }" class="toc-mobile-container">
                <button 
                    @click="open = !open"
                    class="toc-mobile-button w-full px-4 py-3 flex items-center justify-between rounded-lg"
                >
                    <span class="font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        {{ __('blogr::blogr.ui.table_of_contents') }}
                    </span>
                    <svg 
                        class="w-5 h-5 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div 
                    x-show="open" 
                    x-transition
                    class="toc-mobile-dropdown mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg"
                    @click.away="open = false"
                >
                    <div @click="open = false">
                        {!! $tocHtml !!}
                    </div>
                </div>
            </div>
        
            <!-- Layout with sidebar TOC -->
            <div class="grid grid-cols-1 @if($tocPos === 'left') lg:grid-cols-[320px_1fr] @else lg:grid-cols-[1fr_320px] @endif gap-8">
                @if($tocPos === 'left')
                    <!-- Left Sidebar TOC (hidden on mobile, visible on desktop) -->
                    <aside class="toc-sidebar-wrapper">
                        <div class="lg:sticky lg:top-24 lg:max-h-[calc(100vh-8rem)]">
                            {!! $tocHtml !!}
                        </div>
                    </aside>
                @endif

                <!-- Main Article Content -->
                <article class="min-w-0">
        @else
            <!-- Centered layout (default) -->
            <article>
        @endif

        <!-- Translation Warning -->
        @if(isset($displayData) && isset($displayData['translationAvailable']) && !$displayData['translationAvailable'])
            <x-blogr::translation-warning 
                :currentLocale="$currentLocale ?? app()->getLocale()"
                :translationLocale="$displayData['currentTranslationLocale']"
                :availableLocales="$post->translations->pluck('locale')->toArray()"
            />
        @endif
        
        <!-- Language Indicator -->
        @if (isset($availableTranslations) && config('blogr.ui.posts.show_language_switcher', true))
            <div class="mb-6">
                @include('blogr::components.post-language-indicator', [
                    'translations' => $availableTranslations,
                    'currentLocale' => $currentLocale ?? config('blogr.locales.default', 'en'),
                ])
            </div>
        @endif

        <!-- Post Header -->
        <header class="mb-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-gray-900 dark:text-white leading-tight">
                {{ isset($displayData) ? $displayData['title'] : $post->title }}
            </h1>

            <!-- Post Meta -->
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-6">
                @if (config('blogr.ui.dates.show_publication_date', true) && config('blogr.ui.dates.show_publication_date_on_articles', true))
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ $post->published_at?->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') ?? __('blogr::blogr.date.draft') }}
                    </span>
                @endif

                @if (config('blogr.reading_time.enabled', true))
                    <span class="flex items-center">
                        @include('blogr::components.clock-icon')
                        <span
                            class="ml-1">{{ \Happytodev\Blogr\Helpers\ConfigHelper::getReadingTimeText($post->reading_time) }}</span>
                    </span>
                @endif

                <a href="{{ config('blogr.locales.enabled') ? route('blog.category', ['locale' => $currentLocale, 'categorySlug' => $post->category->slug]) : route('blog.category', ['categorySlug' => $post->category->slug]) }}"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[var(--color-category-bg)] dark:bg-[var(--color-category-bg-dark)] text-blue-800 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                    {{ $post->category->name }}
                </a>

                <!-- Author Info -->
                @if(config('blogr.display.show_author_pseudo') || config('blogr.display.show_author_avatar'))
                    <x-blogr::author-info :author="$post->user" size="sm" />
                @endif
            </div>

            <!-- Featured Image -->
            @if ($post->photo)
                <div class="mb-8 rounded-xl overflow-hidden shadow-2xl">
                    <img src="{{ $post->photo_url }}" alt="{{ $post->title }}" class="w-full h-auto">
                </div>
            @else
                <div
                    class="mb-8 rounded-xl overflow-hidden shadow-2xl bg-gradient-to-br from-blue-500 to-purple-600 aspect-video flex items-center justify-center">
                    <img src="{{ asset(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg')) }}"
                        alt="{{ $post->title }}" class="w-full h-full object-cover opacity-30">
                </div>
            @endif
        </header>

        <!-- TL;DR Quote (just after the photo) -->
        {{-- @if ($displayData['tldr'] ?? $post->tldr)
            <div class="mb-8">
                <blockquote class="border-l-4 border-blue-500 pl-6 py-4 bg-gray-50 dark:bg-gray-800/50 rounded-r-lg">
                    <p class="text-lg font-bold italic text-gray-800 dark:text-gray-200">
                        {{ $displayData['tldr'] ?? $post->tldr }}
                    </p>
                </blockquote>
            </div>
        @endif --}}

        <!-- TL;DR Box -->
        @if ($displayData['tldr'] ?? $post->tldr)
            <div class="bg-[var(--color-primary)]/10 dark:bg-[var(--color-primary-dark)]/20 border-l-4 border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] p-6 mb-8 rounded-r-xl">
                <p class="font-bold text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    TL;DR
                </p>
                <p class="text-gray-700 dark:text-gray-300 italic">{{ $displayData['tldr'] ?? $post->tldr }}</p>
            </div>
        @endif

        <!-- Series Box (if part of a series AND series is published) -->
        @if ($post->series && $post->series->isPublished())
            <div
                class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-l-4 border-purple-500 p-6 mb-8 rounded-r-xl shadow-lg">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center flex-grow">
                        <svg class="w-6 h-6 text-purple-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        <div>
                            <div
                                class="text-xs uppercase tracking-wide text-purple-600 dark:text-purple-400 font-semibold mb-1">
                                {{ __('blogr::blogr.series.part_of_series') }}</div>
                            <h3 class="text-lg font-bold text-purple-900 dark:text-purple-300">
                                {{ $post->series->translated_title ?? $post->series->title }}
                            </h3>
                        </div>
                    </div>
                    @if ($post->series->is_featured)
                        <span
                            class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                            ⭐ {{ __('blogr::blogr.ui.featured') }}
                        </span>
                    @endif
                </div>
                <p class="text-gray-700 dark:text-gray-300 text-sm mb-4">
                    {{ $post->series->translated_description ?? $post->series->description }}
                </p>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('blogr::blogr.series.posts_count', ['count' => $post->series->posts->count()]) }}</div>

                <div class="space-y-3 mb-4">
                    @foreach ($post->series->posts->sortBy('series_position') as $seriesPost)
                        <div class="flex items-start">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold mr-3 {{ $seriesPost->id === $post->id ? 'bg-purple-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                {{ $seriesPost->series_position }}
                            </div>
                            <div class="flex-grow">
                                @if ($seriesPost->id === $post->id)
                                    <span
                                        class="font-semibold text-purple-900 dark:text-purple-300">{{ $seriesPost->translated_title ?? $seriesPost->title }}</span>
                                    <span
                                        class="ml-2 text-xs text-purple-600 dark:text-purple-400">({{ __('blogr::blogr.series.current') }})</span>
                                @else
                                    <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $seriesPost->translated_slug ?? $seriesPost->slug]) }}"
                                        class="text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:underline">
                                        {{ $seriesPost->translated_title ?? $seriesPost->title }}
                                    </a>
                                @endif
                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    {{ $seriesPost->published_at?->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-purple-200 dark:border-purple-700">
                    <a href="{{ config('blogr.locales.enabled') ? route('blog.series.index', ['locale' => $currentLocale]) : route('blog.series.index') }}"
                        class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 hover:underline text-sm inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        {{ __('blogr::blogr.ui.view_all_series') }}
                    </a>
                </div>
            </div>
        @endif


        <!-- Author Bio Box (Top Position) -->
        @if(config('blogr.author_bio.enabled', true) && in_array(config('blogr.author_bio.position', 'bottom'), ['top', 'both']))
            <x-blogr::author-bio 
                :author="$post->author" 
                :locale="$currentLocale"
                :compact="config('blogr.author_bio.compact', false)" />
        @endif

        <!-- Tags (Top Position) -->
        @if ($post->tags->count() && config('blogr.ui.posts.tags_position', 'bottom') === 'top')
            <div class="mb-8 flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tags:</span>
                @foreach ($post->tags as $tag)
                    @php
                        $tagTranslation = $tag->translate($currentLocale);
                        $tagName = $tagTranslation ? $tagTranslation->name : $tag->name;
                        $tagSlug = $tagTranslation ? $tagTranslation->slug : $tag->slug;
                    @endphp
                    <a href="{{ config('blogr.locales.enabled') ? route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tagSlug]) : route('blog.tag', ['tagSlug' => $tagSlug]) }}"
                        class="inline-block bg-[var(--color-tag-bg)] dark:bg-[var(--color-tag-bg-dark)] text-gray-900 dark:text-white text-xs px-2.5 py-1 rounded-full hover:opacity-90 transition-colors">
                        #{{ $tagName }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Post Content -->
        <div
            class="prose prose-lg dark:prose-invert max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                    prose-p:text-gray-700 dark:prose-p:text-gray-300
                    prose-a:text-[var(--color-primary)] dark:prose-a:text-[var(--color-primary-dark)] prose-a:no-underline
                    prose-strong:text-gray-900 dark:prose-strong:text-white
                    prose-code:text-pink-600 dark:prose-code:text-pink-400 prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                    prose-pre:bg-gray-900 dark:prose-pre:bg-gray-950 prose-pre:text-gray-100
                    prose-img:rounded-xl prose-img:shadow-lg
                    prose-blockquote:border-[var(--color-primary)] prose-blockquote:bg-[var(--color-primary)]/10 dark:prose-blockquote:bg-[var(--color-primary-dark)]/20 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-r-lg">
            {!! isset($displayData) ? $displayData['content'] : $post->getContentWithoutFrontmatter() !!}
        </div>

        <!-- Tags (Bottom Position) -->
        @if ($post->tags->count() && config('blogr.ui.posts.tags_position', 'bottom') === 'bottom')
            <div class="mb-8 flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tags:</span>
                @foreach ($post->tags as $tag)
                    @php
                        $tagTranslation = $tag->translate($currentLocale);
                        $tagName = $tagTranslation ? $tagTranslation->name : $tag->name;
                        $tagSlug = $tagTranslation ? $tagTranslation->slug : $tag->slug;
                    @endphp
                    <a href="{{ config('blogr.locales.enabled') ? route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tagSlug]) : route('blog.tag', ['tagSlug' => $tagSlug]) }}"
                        class="inline-block bg-[var(--color-tag-bg)] dark:bg-[var(--color-tag-bg-dark)] text-gray-900 dark:text-white text-xs px-2.5 py-1 rounded-full hover:opacity-90 transition-colors">
                        #{{ $tagName }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Author Bio Box (Bottom Position) -->
        @if(config('blogr.author_bio.enabled', true) && in_array(config('blogr.author_bio.position', 'bottom'), ['bottom', 'both']))
            <x-blogr::author-bio 
                :author="$post->author" 
                :locale="$currentLocale"
                :compact="config('blogr.author_bio.compact', false)" />
        @endif

        <!-- Back to Blog Button -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ config('blogr.locales.enabled') ? route('blog.index', ['locale' => $currentLocale]) : route('blog.index') }}"
                class="inline-flex items-center text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] font-semibold group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('blogr::blogr.ui.back_to_all_posts') }}
            </a>
        </div>
    </article>

    @if($hasSidebarToc && $tocPos === 'right')
        <!-- Right Sidebar TOC (hidden on mobile, visible on desktop) -->
        <aside class="toc-sidebar-wrapper">
            <div class="lg:sticky lg:top-24 lg:max-h-[calc(100vh-8rem)]">
                {!! $tocHtml !!}
            </div>
        </aside>
    @endif

    @if($hasSidebarToc)
            </div>
    @endif
    </div>
    
    <!-- Permalink Copy to Clipboard Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize collapsible TOC (collapse entire TOC via title click)
            @if($tocCollapsible ?? true)
            function initCollapsibleToc() {
                // Find all TOC containers (sidebar and inline center)
                // For sidebar: .toc-sidebar-wrapper contains H1 + UL
                // For center: H1 is in prose, followed by UL.toc.blogr-toc-center
                const sidebarTocs = document.querySelectorAll('.toc-sidebar-wrapper');
                const centerTocs = document.querySelectorAll('.prose .toc.blogr-toc-center');
                
                // Process sidebar TOCs
                sidebarTocs.forEach(function(container) {
                    const h1 = container.querySelector('h1');
                    const tocList = container.querySelector('ul.toc, ol.toc');
                    if (h1 && tocList) {
                        setupCollapsibleToc(container, h1, tocList);
                    }
                });
                
                // Process center TOCs (inline in prose)
                centerTocs.forEach(function(tocList) {
                    // Find the H1 that precedes this UL
                    // The H1 should be the previous sibling or nearby
                    let h1 = tocList.previousElementSibling;
                    
                    // Search backwards for H1 with TOC title
                    while (h1 && h1.tagName !== 'H1') {
                        h1 = h1.previousElementSibling;
                    }
                    
                    if (h1) {
                        // Create a wrapper div around H1 + UL for center TOCs
                        const wrapper = document.createElement('div');
                        wrapper.className = 'toc-center-wrapper';
                        h1.parentNode.insertBefore(wrapper, h1);
                        wrapper.appendChild(h1);
                        wrapper.appendChild(tocList);
                        
                        setupCollapsibleToc(wrapper, h1, tocList);
                    }
                });
            }
            
            function setupCollapsibleToc(container, h1, tocList) {
                // Add wrapper class
                if (!container.classList.contains('toc-wrapper-collapsible')) {
                    container.classList.add('toc-wrapper-collapsible');
                }
                
                // Create toggle icon container (span with SVG)
                const toggleIcon = document.createElement('span');
                toggleIcon.className = 'toc-toggle-icon';
                
                // Create SVG element
                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('viewBox', '0 0 24 24');
                svg.setAttribute('fill', 'none');
                
                // Create path element for chevron-down
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
                path.setAttribute('d', 'M19 9l-7 7-7-7');
                
                svg.appendChild(path);
                toggleIcon.appendChild(svg);
                
                // Add icon to h1
                h1.appendChild(toggleIcon);
                
                // Wrap TOC content
                const contentWrapper = document.createElement('div');
                contentWrapper.className = 'toc-content-wrapper';
                tocList.parentNode.insertBefore(contentWrapper, tocList);
                contentWrapper.appendChild(tocList);
                
                // Calculate initial height
                contentWrapper.style.maxHeight = contentWrapper.scrollHeight + 'px';
                
                // Add click handler on H1
                h1.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isCollapsed = toggleIcon.classList.contains('collapsed');
                    
                    // Add bounce animation
                    toggleIcon.classList.add('toggling');
                    setTimeout(function() {
                        toggleIcon.classList.remove('toggling');
                    }, 500); // Match animation duration
                    
                    if (isCollapsed) {
                        // Expand
                        toggleIcon.classList.remove('collapsed');
                        contentWrapper.classList.remove('collapsed');
                        contentWrapper.style.maxHeight = contentWrapper.scrollHeight + 'px';
                    } else {
                        // Collapse
                        toggleIcon.classList.add('collapsed');
                        contentWrapper.classList.add('collapsed');
                        contentWrapper.style.maxHeight = '0';
                    }
                    
                    // Save state to localStorage
                    try {
                        const pageUrl = window.location.pathname;
                        const tocState = JSON.parse(localStorage.getItem('blogr-toc-collapsed-state') || '{}');
                        tocState[pageUrl] = !isCollapsed;
                        localStorage.setItem('blogr-toc-collapsed-state', JSON.stringify(tocState));
                    } catch (e) {
                        // Ignore localStorage errors
                    }
                });
                
                // Restore state from localStorage
                try {
                    const pageUrl = window.location.pathname;
                    const tocState = JSON.parse(localStorage.getItem('blogr-toc-collapsed-state') || '{}');
                    
                    if (tocState[pageUrl] === true) {
                        // Should be collapsed
                        toggleIcon.classList.add('collapsed');
                        contentWrapper.classList.add('collapsed');
                        contentWrapper.style.maxHeight = '0';
                    }
                } catch (e) {
                    // Ignore localStorage errors
                }
            }
            
            initCollapsibleToc();
            @endif
            
            // Copy to clipboard function with fallback
            function copyToClipboard(text) {
                // Try modern clipboard API first (requires HTTPS or localhost)
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(text);
                } else {
                    // Fallback for non-HTTPS contexts
                    return new Promise(function(resolve, reject) {
                        const textArea = document.createElement('textarea');
                        textArea.value = text;
                        textArea.style.cssText = 'position: fixed; left: -999999px; top: -999999px;';
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        
                        try {
                            const successful = document.execCommand('copy');
                            textArea.remove();
                            if (successful) {
                                resolve();
                            } else {
                                reject(new Error('Copy command failed'));
                            }
                        } catch (err) {
                            textArea.remove();
                            reject(err);
                        }
                    });
                }
            }
            
            // Handle permalink clicks
            document.querySelectorAll('.heading-permalink').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the full URL with hash
                    const url = window.location.origin + window.location.pathname + this.getAttribute('href');
                    
                    // Copy to clipboard
                    copyToClipboard(url).then(function() {
                        // Show temporary notification
                        const notification = document.createElement('div');
                        notification.textContent = 'Link copied to clipboard!';
                        notification.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: var(--color-primary);
                            color: white;
                            padding: 12px 24px;
                            border-radius: 8px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            z-index: 10000;
                            animation: slideIn 0.3s ease;
                        `;
                        
                        document.body.appendChild(notification);
                        
                        // Remove notification after 2 seconds
                        setTimeout(function() {
                            notification.style.animation = 'slideOut 0.3s ease';
                            setTimeout(function() {
                                notification.remove();
                            }, 300);
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Failed to copy link:', err);
                    });
                });
            });
            
            // Handle smooth scroll with offset for TOC links and anchor links
            function handleAnchorClick(e) {
                const href = this.getAttribute('href');
                
                // Check if it's an anchor link (starts with #)
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    
                    // Small delay to let mobile menu close first
                    setTimeout(function() {
                        const targetId = href.substring(1);
                        const targetElement = document.getElementById(targetId);
                        
                        if (targetElement) {
                            // Check if navigation is sticky
                            const navEnabled = {{ config('blogr.ui.navigation.enabled', true) ? 'true' : 'false' }};
                            const navSticky = {{ config('blogr.ui.navigation.sticky', true) ? 'true' : 'false' }};
                            
                            // Calculate offset - more on mobile to account for closed menu
                            const isMobile = window.innerWidth < 768;
                            let offset = (navEnabled && navSticky) ? 96 : 16;
                            
                            // Add extra offset on mobile to account for sticky button area
                            if (isMobile) {
                                offset += 80; // Extra space for mobile TOC button area
                            }
                            
                            // Get element position
                            const elementPosition = targetElement.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - offset;
                            
                            // Smooth scroll to position
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                            
                            // Update URL hash
                            history.pushState(null, null, href);
                        }
                    }, 100); // 100ms delay to let Alpine.js close the menu
                }
            }
            
            // Apply to all TOC links
            document.querySelectorAll('.toc a').forEach(function(link) {
                link.addEventListener('click', handleAnchorClick);
            });
            
            // Apply to all anchor links in the page
            document.querySelectorAll('a[href^="#"]').forEach(function(link) {
                // Don't apply to permalink symbols (they copy instead)
                if (!link.classList.contains('heading-permalink')) {
                    link.addEventListener('click', handleAnchorClick);
                }
            });
            
            // Handle initial page load with hash
            if (window.location.hash) {
                setTimeout(function() {
                    const targetId = window.location.hash.substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        const navEnabled = {{ config('blogr.ui.navigation.enabled', true) ? 'true' : 'false' }};
                        const navSticky = {{ config('blogr.ui.navigation.sticky', true) ? 'true' : 'false' }};
                        const offset = (navEnabled && navSticky) ? 96 : 16;
                        
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                }, 100); // Small delay to ensure page is fully loaded
            }
        });
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
@endsection
