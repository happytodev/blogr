@extends('blogr::layouts.blog')

@section('seo-data')
    @php $seoData = [
        'title' => __('blogr::blogr.feeds.title', [], $currentLocale),
        'description' => __('blogr::blogr.feeds.description', [], $currentLocale),
    ]; @endphp
@endsection

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ __('blogr::blogr.feeds.title', [], $currentLocale) }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-10">{{ __('blogr::blogr.feeds.description', [], $currentLocale) }}</p>

        <div class="space-y-10">
            {{-- Main Feed --}}
            <section>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"/>
                    </svg>
                    <span>{{ __('blogr::blogr.feeds.main_feed', [], $currentLocale) }}</span>
                </h2>
                <a href="{{ $mainFeedUrl }}"
                   class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-[var(--color-primary)] dark:hover:border-[var(--color-primary-dark)] transition-colors group">
                    <div>
                        <span class="font-medium text-gray-900 dark:text-white group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] transition-colors">
                            {{ __('blogr::blogr.feeds.all_posts', [], $currentLocale) }}
                        </span>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ __('blogr::blogr.feeds.main_feed_desc', [], $currentLocale) }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </section>

            {{-- Category Feeds --}}
            @if($categories->isNotEmpty())
            <section>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"/>
                    </svg>
                    <span>{{ __('blogr::blogr.feeds.categories', [], $currentLocale) }}</span>
                </h2>
                <div class="space-y-2">
                    @foreach($categories as $category)
                    <a href="{{ $category['url'] }}"
                       class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-[var(--color-primary)] dark:hover:border-[var(--color-primary-dark)] transition-colors group">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0 fill-[var(--color-primary)] dark:fill-[var(--color-primary-dark)]" viewBox="0 0 24 24">
                                <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-white truncate group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] transition-colors">
                                {{ $category['translatedName'] ?? $category['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $category['postsCount'] }} {{ __('blogr::blogr.feeds.posts', [], $currentLocale) }}</span>
                            <svg class="w-5 h-5 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Tag Feeds --}}
            @if($tags->isNotEmpty())
            <section>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"/>
                    </svg>
                    <span>{{ __('blogr::blogr.feeds.tags', [], $currentLocale) }}</span>
                </h2>
                <div class="space-y-2">
                    @foreach($tags as $tag)
                    <a href="{{ $tag['url'] }}"
                       class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-[var(--color-primary)] dark:hover:border-[var(--color-primary-dark)] transition-colors group">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0 fill-[var(--color-primary)] dark:fill-[var(--color-primary-dark)]" viewBox="0 0 24 24">
                                <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-white truncate group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] transition-colors">
                                {{ $tag['translatedName'] ?? $tag['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $tag['postsCount'] }} {{ __('blogr::blogr.feeds.posts', [], $currentLocale) }}</span>
                            <svg class="w-5 h-5 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>
</div>
@endsection