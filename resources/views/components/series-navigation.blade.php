@props(['post', 'currentLocale' => null])

@php
    use Happytodev\Blogr\Models\BlogPost;
    
    if (!$post instanceof BlogPost || !$post->blog_series_id) {
        return;
    }
    
    $series = $post->series;
    $currentLocale = $currentLocale ?? app()->getLocale() ?? config('blogr.locales.default', 'en');
    $previous = $post->previousInSeries();
    $next = $post->nextInSeries();
    $currentLocale = app()->getLocale();
    
    // Get translations for previous and next posts
    $previousTranslation = $previous?->translate($currentLocale) ?? $previous?->getDefaultTranslation();
    $nextTranslation = $next?->translate($currentLocale) ?? $next?->getDefaultTranslation();
    $seriesTranslation = $series?->translate($currentLocale) ?? $series?->getDefaultTranslation();
@endphp

@if($series && ($previous || $next))
<nav class="series-navigation bg-gradient-to-r from-[var(--color-series-card-bg)] to-indigo-50 dark:from-[var(--color-series-card-bg-dark)] dark:to-gray-900 border border-[var(--color-primary)]/30 dark:border-[var(--color-primary-dark)]/30 rounded-lg p-6 my-8" {{ $attributes }}>
    <div class="mb-4">
        <div class="flex items-center text-sm text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] font-semibold mb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Part of a series
        </div>
        <h3 class="text-xl font-bold text-gray-900">
            {{ $seriesTranslation?->title ?? $series->slug }}
        </h3>
        @if($seriesTranslation?->description)
        <p class="text-sm text-gray-600 mt-1">{{ $seriesTranslation->description }}</p>
        @endif
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        @if($previous)
        <a href="{{ route('blog.show', ['slug' => $previousTranslation?->slug ?? $previous->slug]) }}" 
           class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-[var(--color-primary)] dark:hover:border-[var(--color-primary-dark)] hover:shadow-md transition-all group">
            <div class="flex-shrink-0 mr-3">
                <svg class="w-6 h-6 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </div>
            <div class="flex-grow">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Previous</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] line-clamp-2">
                    {{ $previousTranslation?->title ?? 'Previous post' }}
                </div>
            </div>
        </a>
        @else
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg opacity-50">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Previous</div>
            <div class="text-sm text-gray-400">No previous post</div>
        </div>
        @endif
        
        @if($next)
        <a href="{{ route('blog.show', ['slug' => $nextTranslation?->slug ?? $next->slug]) }}" 
           class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-[var(--color-primary)] dark:hover:border-[var(--color-primary-dark)] hover:shadow-md transition-all group">
            <div class="flex-grow text-right">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Next</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] line-clamp-2">
                    {{ $nextTranslation?->title ?? 'Next post' }}
                </div>
            </div>
            <div class="flex-shrink-0 ml-3">
                <svg class="w-6 h-6 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @else
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg opacity-50 text-right">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Next</div>
            <div class="text-sm text-gray-400">No next post</div>
        </div>
        @endif
    </div>
    
    <div class="mt-4 pt-4 border-t border-blue-200">
        <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->slug]) }}" 
           class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            View all posts in this series
        </a>
    </div>
</nav>
@endif
