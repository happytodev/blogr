@props(['series', 'currentLocale' => null])

@php
    $currentLocale = $currentLocale ?? app()->getLocale();
@endphp

<div class="group bg-[var(--color-series-card-bg)] dark:bg-[var(--color-series-card-bg-dark)] rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1">
    <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->translated_slug ?? $series->slug]) }}"
        class="block">
        <div class="relative h-48 overflow-hidden">
            @if ($series->photo_url)
                <img src="{{ $series->photo_url }}"
                    alt="{{ $series->translated_title ?? $series->slug }}"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
            @else
                <img src="{{ asset(config('blogr.series.default_image', '/vendor/blogr/images/default-series.svg')) }}"
                    alt="{{ $series->translated_title ?? $series->slug }}"
                    class="w-full h-full object-cover opacity-50">
            @endif
            <div class="absolute bottom-4 left-4 right-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] text-white">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                    {{ $series->posts->count() > 1 ? __('blogr::blogr.series.posts_count', ['count' => $series->posts->count()]) : __('blogr::blogr.ui.read_post') }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] transition-colors">
                {{ $series->translated_title ?? $series->slug }}
            </h3>

            @if ($series->translated_description)
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                    {{ $series->translated_description }}
                </p>
            @endif

            {{-- Authors + View Series Link --}}
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                {{-- Series Authors --}}
                @if (config('blogr.display.show_series_authors'))
                    @php
                        $seriesAuthors = $series->authors();
                    @endphp
                    @if (count($seriesAuthors) > 0)
                        <div class="flex-shrink-0">
                            <x-blogr::series-authors :authors="$seriesAuthors" size="sm" />
                        </div>
                    @endif
                @endif

                {{-- View Series Link --}}
                <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->translated_slug ?? $series->slug]) }}"
                    class="inline-flex items-center text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] font-semibold text-sm group/link ml-auto"
                    onclick="event.stopPropagation();">
                    {{ __('blogr::blogr.series.view_series') }}
                    <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </a>
</div>
