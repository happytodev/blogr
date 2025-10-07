@props(['post', 'showTitle' => true, 'currentLocale' => null])

@php
    use Happytodev\Blogr\Models\BlogPost;
    
    if (!$post instanceof BlogPost || !$post->blog_series_id) {
        return;
    }
    
    $series = $post->series;
    $currentLocale = $currentLocale ?? app()->getLocale() ?? config('blogr.locales.default', 'en');
    $currentLocale = app()->getLocale();
    $seriesTranslation = $series?->translate($currentLocale) ?? $series?->getDefaultTranslation();
    
    // Get position in series
    $totalPosts = $series->posts()->count();
    $position = $post->series_position ?? 1;
@endphp

@if($series)
<div class="series-badge inline-flex items-center" {{ $attributes }}>
    <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->slug]) }}" 
       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        @if($showTitle)
            <span class="mr-1">{{ $seriesTranslation?->title ?? $series->slug }}</span>
        @endif
        <span class="opacity-75">· Part {{ $position }}/{{ $totalPosts }}</span>
    </a>
    
    @if($series->is_featured)
        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </span>
    @endif
</div>
@endif
