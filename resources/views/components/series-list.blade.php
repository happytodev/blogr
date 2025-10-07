@props(['series', 'currentPost' => null])

@php
    use Happytodev\Blogr\Models\BlogSeries;
    
    if (!$series instanceof BlogSeries) {
        return;
    }
    
    $currentLocale = app()->getLocale();
    $seriesTranslation = $series->translate($currentLocale) ?? $series->getDefaultTranslation();
    $posts = $series->posts()->orderBy('series_position')->get();
@endphp

@if($posts->count() > 0)
<div class="series-list bg-white border border-gray-200 rounded-lg shadow-sm p-6 my-8" {{ $attributes }}>
    <div class="mb-6">
        <div class="flex items-center text-sm text-indigo-600 font-semibold mb-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            {{ $seriesTranslation?->title ?? $series->slug }}
        </div>
        @if($seriesTranslation?->description)
        <p class="text-gray-600">{{ $seriesTranslation->description }}</p>
        @endif
        <p class="text-sm text-gray-500 mt-2">
            {{ $posts->count() }} {{ $posts->count() === 1 ? 'post' : 'posts' }} in this series
        </p>
    </div>
    
    <ol class="space-y-3">
        @foreach($posts as $index => $post)
            @php
                $postTranslation = $post->translate($currentLocale) ?? $post->getDefaultTranslation();
                $isCurrentPost = $currentPost && $currentPost->id === $post->id;
                $isPublished = $post->isCurrentlyPublished();
            @endphp
            
            <li class="flex items-start {{ $isCurrentPost ? 'bg-blue-50 border border-blue-200 rounded-lg p-3' : '' }}">
                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full {{ $isCurrentPost ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600' }} font-semibold text-sm mr-3">
                    {{ $index + 1 }}
                </div>
                
                <div class="flex-grow">
                    @if($isPublished)
                        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $postTranslation?->slug ?? $post->slug]) }}" 
                           class="group">
                            <h4 class="text-base font-semibold {{ $isCurrentPost ? 'text-blue-700' : 'text-gray-900 group-hover:text-blue-600' }} transition-colors">
                                {{ $postTranslation?->title ?? 'Untitled' }}
                                @if($isCurrentPost)
                                    <span class="ml-2 text-xs font-normal text-blue-600">(Current)</span>
                                @endif
                            </h4>
                            @if($postTranslation?->seo_description)
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    {{ $postTranslation->seo_description }}
                                </p>
                            @endif
                        </a>
                    @else
                        <div class="opacity-50">
                            <h4 class="text-base font-semibold text-gray-500">
                                {{ $postTranslation?->title ?? 'Untitled' }}
                                <span class="ml-2 text-xs font-normal text-gray-400">(Unpublished)</span>
                            </h4>
                        </div>
                    @endif
                    
                    @if($post->published_at && $isPublished)
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $post->published_at->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') }}
                            @if($postTranslation && $postTranslation->reading_time)
                                · {{ $postTranslation->reading_time }} min read
                            @endif
                        </div>
                    @endif
                </div>
                
                @if($isCurrentPost)
                    <div class="flex-shrink-0 ml-3">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
    
    @if($series->is_featured)
    <div class="mt-6 pt-4 border-t border-gray-200">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Featured Series
        </span>
    </div>
    @endif
</div>
@endif
