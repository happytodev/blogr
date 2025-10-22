@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@section('content')
    @php
        $currentLocale = app()->getLocale();
    @endphp

    <div class="container mx-auto px-4 py-12">
        <!-- Page Header -->
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-bold mb-4 text-gray-900 dark:text-white">
                {{ \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultTitle($currentLocale) }}</h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultDescription($currentLocale) }}
            </p>
        </div>

        <!-- Featured Series Section -->
        @if (isset($featuredSeries) && $featuredSeries->count() > 0)
            <div class="mb-16">
                <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">
                    <svg class="inline-block w-8 h-8 mr-2 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                    {{ __('blogr::blogr.series.featured_series') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($featuredSeries as $series)
                        <x-blogr::series-card :series="$series" :currentLocale="$currentLocale" />
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Posts Grid -->
        <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">{{ __('blogr::blogr.ui.latest_posts') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 auto-rows-fr">
            @forelse ($posts as $post)
                <x-blogr::blog-post-card :post="$post" :currentLocale="$currentLocale" />
            @empty
                <div class="col-span-full text-center py-20">
                    <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-xl">{{ __('blogr::blogr.ui.no_posts_yet') }}</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">{{ __('blogr::blogr.ui.check_back_soon') }}
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        @if ($posts->hasPages())
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
