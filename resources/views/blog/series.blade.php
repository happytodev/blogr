@extends('blogr::layouts.blog')

@section('title', $seoData['title'] ?? __('blogr::blogr.series.title'))
@section('meta_description', $seoData['description'] ?? '')

@section('content')
<div class="container mx-auto px-4 py-12">
    {{-- Breadcrumb --}}
    <x-blogr::breadcrumb :items="[
        ['label' => $seriesTranslation?->title ?? $series->slug, 'url' => null]
    ]" />
    
    {{-- Series Header --}}
    <div class="mb-12 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg overflow-hidden">
        {{-- Series Image --}}
        <div class="relative h-64 w-full overflow-hidden">
            @if($series->photo_url)
                <img src="{{ $series->photo_url }}" 
                     alt="{{ $seriesTranslation?->title ?? $series->slug }}"
                     class="w-full h-full object-cover">
            @else
                <img src="{{ asset(config('blogr.series.default_image', '/vendor/blogr/images/default-series.svg')) }}" 
                     alt="{{ $seriesTranslation?->title ?? $series->slug }}"
                     class="w-full h-full object-cover opacity-50">
            @endif
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/30"></div>
        </div>
        
        <div class="p-8">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-blue-600 dark:bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                
                <div class="flex-grow">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                        {{ $seriesTranslation?->title ?? $series->slug }}
                    </h1>
                    @if($series->is_featured)
                        <span class="px-3 py-1 text-sm font-bold text-white dark:text-gray-600 bg-yellow-500 dark:bg-yellow-200 rounded-full shadow-lg">
                            ⭐ {{ __('blogr::blogr.series.featured') }}
                        </span>
                    @endif
                </div>
                
                @if($seriesTranslation?->description)
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-4">{{ $seriesTranslation->description }}</p>
                @endif
                
                {{-- Series Authors --}}
                @if(config('blogr.display.show_series_authors'))
                    @php
                        $seriesAuthors = $series->authors();
                    @endphp
                    @if(count($seriesAuthors) > 0)
                    <div class="mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('blogr::blogr.series.authors') }}:</span>
                            <x-blogr::series-authors :authors="$seriesAuthors" size="sm" />
                        </div>
                    </div>
                    @endif
                @endif
                
                <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('blogr::blogr.series.posts_count', ['count' => $posts->count()]) }}
                    </span>
                    @if($series->published_at)
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ __('blogr::blogr.series.started_on', ['date' => $series->published_at->translatedFormat('M d, Y')]) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Series List Component --}}
    <x-blogr::series-list :series="$series" class="mb-12" />
    
    {{-- Posts Grid --}}
    @if($posts->count() > 0)
    <div class="mt-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">{{ __('blogr::blogr.series.all_posts_in_series') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 auto-rows-fr">
            @foreach($posts as $post)
                @php
                    $currentLocale = app()->getLocale();
                    $postTranslation = $post->translate($currentLocale) ?? $post->getDefaultTranslation();
                @endphp
                
                <article class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 flex flex-col h-full">
                    <!-- Post Image -->
                    <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden">
                        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug ?? $postTranslation?->slug ?? $post->slug]) }}" class="block h-full">
                            @if($post->photo_url)
                                <img src="{{ $post->photo_url }}" 
                                     alt="{{ $postTranslation?->title }}" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <img src="{{ asset(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg')) }}" 
                                     alt="{{ $postTranslation?->title }}"
                                     class="absolute inset-0 w-full h-full object-cover opacity-50">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        
                        <!-- Part Number Badge -->
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white shadow-lg">
                                {{ __('blogr::blogr.series.part_number', ['number' => $post->series_position]) }}
                            </span>
                        </div>
                        
                        <!-- Reading Time Badge -->
                        @if(config('blogr.reading_time.enabled', true))
                        <div class="absolute top-4 right-4 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium text-white flex items-center">
                            @include('blogr::components.clock-icon')
                            <span class="ml-1">
                                @if($postTranslation?->reading_time)
                                    {{ $postTranslation->reading_time }} {{ $postTranslation->reading_time > 1 ? 'mins' : 'min' }}
                                @else
                                    &lt; 1 min
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Post Content -->
                    <div class="p-6 flex-grow flex flex-col">
                        <!-- Date -->
                        @if($post->published_at)
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            {{ $post->published_at->translatedFormat('M d, Y') }}
                        </div>
                        @endif
                        
                        <!-- Title -->
                        <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2">
                            <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug ?? $postTranslation?->slug ?? $post->slug]) }}">
                                {{ $postTranslation?->title ?? __('blogr::blogr.ui.untitled') }}
                            </a>
                        </h3>
                        
                        <!-- Description -->
                        @if($postTranslation?->seo_description)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                                {{ $postTranslation->seo_description }}
                            </p>
                        @endif
                        
                        <!-- Bottom Section: Author + Read More (always at bottom) -->
                        <div class="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <!-- Author Info -->
                            @if($post->user)
                                <div class="flex-shrink-0">
                                    <x-blogr::author-info :author="$post->user" size="sm" />
                                </div>
                            @endif
                            
                            <!-- Read More Link -->
                            <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug ?? $postTranslation?->slug ?? $post->slug]) }}" 
                               class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold text-sm group/link ml-auto">
                                {{ __('blogr::blogr.ui.read_post') }}
                                <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
