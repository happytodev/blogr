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
    .prose .toc {
        background-color: rgb(249 250 251);
        border-left: 4px solid rgb(59 130 246);
        padding: 1rem;
        border-radius: 0 0.5rem 0.5rem 0;
        margin-bottom: 1.5rem;
    }
    
    .dark .prose .toc {
        background-color: rgb(31 41 55 / 0.5);
        border-left-color: rgb(96 165 250);
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
</style>
@endpush

@section('content')
    <article class="container mx-auto px-4 py-12 max-w-4xl">
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
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ $post->published_at?->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') ?? __('blogr::blogr.date.draft') }}
                </span>

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


        <!-- Tags -->
        @if ($post->tags->count())
            <div class="mb-8 flex flex-wrap gap-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tags:</span>
                @foreach ($post->tags as $tag)
                    @php
                        $tagTranslation = $tag->translate($currentLocale);
                        $tagName = $tagTranslation ? $tagTranslation->name : $tag->name;
                        $tagSlug = $tagTranslation ? $tagTranslation->slug : $tag->slug;
                    @endphp
                    <a href="{{ config('blogr.locales.enabled') ? route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tagSlug]) : route('blog.tag', ['tagSlug' => $tagSlug]) }}"
                        {{-- class="inline-block bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 text-sm px-3 py-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"> --}}
                        class="inline-block bg-[var(--color-tag-bg)] dark:bg-[var(--color-tag-bg-dark)] text-gray-900 dark:text-white text-xs px-2.5 py-1 rounded-full hover:opacity-90 transition-colors">
                        #{{ $tagName }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Author Bio Box (Top Position) -->
        @if(config('blogr.author_bio.enabled', true) && in_array(config('blogr.author_bio.position', 'bottom'), ['top', 'both']))
            <x-blogr::author-bio 
                :author="$post->author" 
                :locale="$currentLocale"
                :compact="config('blogr.author_bio.compact', false)" />
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
    
    <!-- Permalink Copy to Clipboard Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle permalink clicks
            document.querySelectorAll('.heading-permalink').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the full URL with hash
                    const url = window.location.origin + window.location.pathname + this.getAttribute('href');
                    
                    // Copy to clipboard
                    navigator.clipboard.writeText(url).then(function() {
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
                    
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        // Check if navigation is sticky
                        const navEnabled = {{ config('blogr.ui.navigation.enabled', true) ? 'true' : 'false' }};
                        const navSticky = {{ config('blogr.ui.navigation.sticky', true) ? 'true' : 'false' }};
                        
                        // Calculate offset (96px = 6rem for sticky nav, 16px = 1rem otherwise)
                        const offset = (navEnabled && navSticky) ? 96 : 16;
                        
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
