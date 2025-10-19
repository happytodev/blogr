@extends('blogr::layouts.blog')

@section('title', $seoData['title'] ?? __('blogr::blogr.series.title'))
@section('meta_description', $seoData['description'] ?? '')

@section('content')
    <div class="container mx-auto px-4 py-12">
        {{-- Breadcrumb --}}
        <x-blogr::breadcrumb :items="[['label' => $seriesTranslation?->title ?? $series->slug, 'url' => null]]" />

        {{-- Series Header --}}
        <div
            class="mb-12 bg-gradient-to-br from-[var(--color-series-card-bg)] to-indigo-50 dark:from-[var(--color-series-card-bg-dark)] dark:to-gray-900 rounded-xl shadow-lg overflow-hidden xl:w-9/12 xl:mx-auto">
            {{-- Series Image --}}
            {{-- <div class="relative h-64 w-full overflow-hidden"> --}}
            <div class="max-h-3/5 overflow-hidden rounded-t-xl w-full">
                @if ($series->photo_url)
                    <img src="{{ $series->photo_url }}" alt="{{ $seriesTranslation?->title ?? $series->slug }}"
                        class="w-full h-full object-cover object-center">
                @else
                    <img src="{{ asset(config('blogr.series.default_image', '/vendor/blogr/images/default-series.svg')) }}"
                        alt="{{ $seriesTranslation?->title ?? $series->slug }}" class="w-full h-full object-cover opacity-50">
                @endif
                {{-- <div class="inset-0 bg-gradient-to-b from-transparent to-black/30"></div> --}}
            </div>

            <div class="p-8">
                <div class="flex items-start gap-4 mb-4">
                    <div class="flex-shrink-0">
                        <div
                            class="w-4 h-4 xl:w-16 xl:h-16 bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-grow">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-xl xl:text-4xl font-bold text-gray-900 dark:text-white">
                                {{ $seriesTranslation?->title ?? $series->slug }}
                            </h1>
                            {{-- @if ($series->is_featured)
                                <span
                                    class="px-3 py-1 text-sm font-bold text-white dark:text-gray-600 bg-yellow-500 dark:bg-yellow-200 rounded-full shadow-lg">
                                    ⭐ {{ __('blogr::blogr.series.featured') }}
                                </span>
                            @endif --}}
                        </div>

                        @if ($seriesTranslation?->description)
                            <p class="text-lg text-gray-600 dark:text-gray-300 mb-4">{{ $seriesTranslation->description }}
                            </p>
                        @endif

                        {{-- Series Authors --}}
                        @if (config('blogr.display.show_series_authors'))
                            @php
                                $seriesAuthors = $series->authors();
                            @endphp
                            @if (count($seriesAuthors) > 0)
                                <div class="mb-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('blogr::blogr.series.authors') }}:</span>
                                        <x-blogr::series-authors :authors="$seriesAuthors" size="sm" />
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('blogr::blogr.series.posts_count', ['count' => $posts->count()]) }}
                            </span>
                            @if ($series->published_at)
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('blogr::blogr.series.started_on', ['date' => $series->published_at->translatedFormat('M d, Y')]) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Series List Component --}}
        <x-blogr::series-list :series="$series" class="mb-12" />

        {{-- Posts Grid --}}
        @if ($posts->count() > 0)
            <div class="mt-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                    {{ __('blogr::blogr.series.all_posts_in_series') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 auto-rows-fr">
                    @foreach ($posts as $post)
                        <x-blogr::blog-post-card :post="$post" :currentLocale="$currentLocale" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
