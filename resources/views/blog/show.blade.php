@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@section('content')
    <article class="container mx-auto px-4 py-12 max-w-4xl">
        <!-- Language Indicator -->
        @if (isset($availableTranslations) && config('blogr.posts.show_language_switcher', true))
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

                <a href="{{ route('blog.category', ['locale' => $currentLocale, 'categorySlug' => $post->category->slug]) }}"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                    {{ $post->category->name }}
                </a>
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
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-6 mb-8 rounded-r-xl">
                <p class="font-bold text-blue-900 dark:text-blue-300 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    TL;DR
                </p>
                <p class="text-gray-700 dark:text-gray-300 italic">{{ $displayData['tldr'] ?? $post->tldr }}</p>
            </div>
        @endif

        <!-- Series Box (if part of a series) -->
        @if ($post->series)
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
                                    <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $seriesPost->translated_slug]) }}"
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
                    <a href="{{ route('blog.series.index', ['locale' => $currentLocale]) }}"
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
                    <a href="{{ route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tag->slug]) }}"
                        class="inline-block bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300 text-sm px-3 py-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Post Content -->
        <div
            class="prose prose-lg dark:prose-invert max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                    prose-p:text-gray-700 dark:prose-p:text-gray-300
                    prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-900 dark:prose-strong:text-white
                    prose-code:text-pink-600 dark:prose-code:text-pink-400 prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                    prose-pre:bg-gray-900 dark:prose-pre:bg-gray-950 prose-pre:text-gray-100
                    prose-img:rounded-xl prose-img:shadow-lg
                    prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 dark:prose-blockquote:bg-blue-900/20 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-r-lg">
            {!! isset($displayData) ? $displayData['content'] : $post->getContentWithoutFrontmatter() !!}
        </div>

        <!-- Back to Blog Button -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}"
                class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('blogr::blogr.ui.back_to_all_posts') }}
            </a>
        </div>
    </article>
@endsection
