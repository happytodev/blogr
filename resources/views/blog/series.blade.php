@extends('blogr::layouts.blog')

@section('title', $seoData['title'] ?? __('blogr::blogr.series.title'))
@section('meta_description', $seoData['description'] ?? '')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Breadcrumb --}}
    <x-blogr::breadcrumb :items="[
        ['label' => $seriesTranslation?->title ?? $series->slug, 'url' => null]
    ]" />
    
    {{-- Series Header --}}
    <div class="mb-8">
        <div class="flex items-center mb-3">
            <svg class="w-8 h-8 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h1 class="text-4xl font-bold text-gray-900">
                {{ $seriesTranslation?->title ?? $series->slug }}
            </h1>
            @if($series->is_featured)
                <span class="ml-3 px-3 py-1 text-sm font-semibold text-white bg-indigo-600 rounded-full">
                    {{ __('blogr::blogr.series.featured') }}
                </span>
            @endif
        </div>
        
        @if($seriesTranslation?->description)
            <p class="text-lg text-gray-700 mb-4">{{ $seriesTranslation->description }}</p>
        @endif
        
        <div class="flex items-center space-x-4 text-sm text-gray-600">
            <span class="inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('blogr::blogr.series.posts_count', ['count' => $posts->count()]) }}
            </span>
            @if($series->published_at)
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('blogr::blogr.series.started_on', ['date' => $series->published_at->translatedFormat('M d, Y')]) }}
                </span>
            @endif
        </div>
    </div>
    
    {{-- Series List Component --}}
    <x-blogr::series-list :series="$series" class="mb-12" />
    
    {{-- Posts Grid --}}
    @if($posts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('blogr::blogr.series.all_posts_in_series') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                @php
                    $currentLocale = app()->getLocale();
                    $postTranslation = $post->translate($currentLocale) ?? $post->getDefaultTranslation();
                @endphp
                
                <article class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    @if($post->photo_url)
                        <img src="{{ $post->photo_url }}" alt="{{ $postTranslation?->title }}" class="w-full h-48 object-cover">
                    @endif
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ __('blogr::blogr.series.part_number', ['number' => $post->series_position]) }}
                            </span>
                            @if($post->published_at)
                                <span class="text-xs text-gray-500">
                                    {{ $post->published_at->translatedFormat('M d, Y') }}
                                </span>
                            @endif
                        </div>
                        
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2">
                            <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug ?? $postTranslation?->slug ?? $post->slug]) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $postTranslation?->title ?? __('blogr::blogr.ui.untitled') }}
                            </a>
                        </h3>
                        
                        @if($postTranslation?->seo_description)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $postTranslation->seo_description }}
                            </p>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug ?? $postTranslation?->slug ?? $post->slug]) }}" 
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium inline-flex items-center">
                                {{ __('blogr::blogr.ui.read_post') }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            
                            @if($postTranslation && $postTranslation->reading_time)
                                <span class="text-xs text-gray-500">
                                    {{ __('blogr::blogr.ui.reading_time', ['time' => $postTranslation->reading_time]) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
