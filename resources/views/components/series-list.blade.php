@props(['series', 'currentPost' => null])

@php
    use Happytodev\Blogr\Models\BlogSeries;
    
    if (!$series instanceof BlogSeries) {
        return;
    }
    
    $currentLocale = app()->getLocale();
    $seriesTranslation = $series->translate($currentLocale) ?? $series->getDefaultTranslation();
    $posts = $series->posts()
        ->published()
        ->orderBy('series_position')
        ->get();

    $maxVisible = config('blogr.series.max_visible_posts', 10);
    $extraCount = max(0, $posts->count() - $maxVisible);
@endphp

@if($posts->count() > 0)
<div class="xl:w-9/12 xl:mx-auto bg-gradient-to-r from-[var(--color-primary)]/5 to-pink-50 dark:from-[var(--color-primary-dark)]/20 dark:to-pink-900/20 border-l-4 border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] p-6 mb-8 rounded-r-xl shadow-lg" {{ $attributes }}>
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-center flex-grow">
            <svg class="w-6 h-6 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <div>
                <div class="text-xs uppercase tracking-wide text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] font-semibold mb-1">
                    {{ __('blogr::blogr.series.series') }}
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                    {{ $seriesTranslation?->title ?? $series->slug }}
                </h3>
            </div>
        </div>
        @if($series->is_featured)
            <span class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                ⭐ {{ __('blogr::blogr.ui.featured') }}
            </span>
        @endif
    </div>
    
    @if($seriesTranslation?->description)
        <p class="text-gray-700 dark:text-gray-300 text-sm mb-4">
            {{ $seriesTranslation->description }}
        </p>
    @endif
    
    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        {{ __('blogr::blogr.series.posts_count', ['count' => $posts->count()]) }}
    </div>

    @php $shownCount = $extraCount > 0 ? $maxVisible : $posts->count(); @endphp
    <div class="space-y-3 mb-4" x-data="{ showAll: false }">
        @foreach($posts as $i => $post)
            @php
                $postTranslation = $post->translate($currentLocale) ?? $post->getDefaultTranslation();
                $isCurrentPost = $currentPost && $currentPost->id === $post->id;
            @endphp
            
            <div class="flex items-start" x-show="showAll || {{ $i < $maxVisible ? 'true' : 'false' }}" x-transition:enter.duration.200ms>
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold mr-3 {{ $isCurrentPost ? 'bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                    {{ $post->series_position }}
                </div>
                <div class="flex-grow">
                    @if($isCurrentPost)
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $postTranslation?->title ?? __('blogr::blogr.ui.untitled') }}</span>
                        <span class="ml-2 text-xs text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">({{ __('blogr::blogr.series.current') }})</span>
                    @else
                        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $postTranslation?->slug ?? $post->slug]) }}" 
                           class="relative z-10 text-gray-700 dark:text-gray-300 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] hover:underline">
                            {{ $postTranslation?->title ?? __('blogr::blogr.ui.untitled') }}
                        </a>
                    @endif
                    <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                        {{ $post->published_at?->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') }}
                    </div>
                </div>
            </div>
        @endforeach

        @if($extraCount > 0)
            <button @click="showAll = !showAll" type="button"
                    class="text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] hover:underline text-sm inline-flex items-center gap-1 mt-2 cursor-pointer">
                <svg x-show="!showAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <svg x-show="showAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
                <span x-show="!showAll">{{ __('blogr::blogr.series.show_more_posts', ['count' => $extraCount]) }}</span>
                <span x-show="showAll" x-cloak>{{ __('blogr::blogr.series.show_less_posts') }}</span>
            </button>
        @endif
    </div>

    <div class="pt-4 border-t border-[var(--color-primary)]/30 dark:border-[var(--color-primary-dark)]/50">
        <a href="{{ route('blog.series.index', ['locale' => $currentLocale]) }}" 
           class="relative z-10 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] hover:underline text-sm inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            {{ __('blogr::blogr.series.view_all_series') }}
        </a>
    </div>
</div>
@endif
