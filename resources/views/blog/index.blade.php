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
            <h1 class="text-5xl font-bold mb-4 text-gray-900 dark:text-white">{{ \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultTitle($currentLocale) }}</h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ \Happytodev\Blogr\Helpers\ConfigHelper::getSeoDefaultDescription($currentLocale) }}
            </p>
        </div>

        <!-- Featured Series Section -->
        @if(isset($featuredSeries) && $featuredSeries->count() > 0)
        <div class="mb-16">
            <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">
                <svg class="inline-block w-8 h-8 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Featured Series
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredSeries as $series)
                <div class="group bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 border-2 border-blue-200 dark:border-blue-800">
                    @if($series->photo)
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ $series->photo_url }}" 
                             alt="{{ $series->translated_title ?? $series->slug }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ $series->posts->count() }} articles
                            </span>
                        </div>
                    </div>
                    @endif
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->slug]) }}">
                                {{ $series->translated_title ?? $series->slug }}
                            </a>
                        </h3>
                        
                        @if($series->translated_description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                            {{ $series->translated_description }}
                        </p>
                        @endif
                        
                        <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->slug]) }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold text-sm group/link">
                            {{ __('blogr::blogr.series.view_series') }}
                            <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Posts Grid -->
        <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Latest Articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($posts as $post)
                <article class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                    <!-- Post Image -->
                    <div class="relative h-56 bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden">
                        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug]) }}" class="block h-full">
                            @if ($post->photo)
                                <img src="{{ $post->photo_url }}" 
                                     alt="{{ $post->title }}"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <img src="{{ asset(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg')) }}" 
                                     alt="{{ $post->title }}"
                                     class="absolute inset-0 w-full h-full object-cover opacity-50">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </a>
                        
                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4">
                            <a href="{{ config('blogr.locales.enabled') ? route('blog.category', ['locale' => $currentLocale, 'categorySlug' => $post->category->slug]) : route('blog.category', ['categorySlug' => $post->category->slug]) }}"
                               class="inline-block bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold text-gray-900 dark:text-white hover:bg-white dark:hover:bg-gray-900 transition-colors">
                                {{ $post->category->name }}
                            </a>
                        </div>

                        <!-- Reading Time Badge -->
                        @if(config('blogr.reading_time.enabled', true))
                        <div class="absolute top-4 right-4 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium text-white flex items-center">
                            @include('blogr::components.clock-icon')
                            <span class="ml-1">{{ $post->getFormattedReadingTime() }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Post Content -->
                    <div class="p-6 flex-grow flex flex-col">
                        <!-- Title -->
                        <h2 class="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-2">
                            <a href="{{ config('blogr.locales.enabled') ? route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug]) : route('blog.show', ['slug' => $post->translated_slug]) }}">
                                {{ $post->translated_title ?? $post->title }}
                            </a>
                        </h2>

                        <!-- Excerpt or TL;DR -->
                        @if ($post->translated_excerpt ?? $post->excerpt)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3 flex-grow">
                                {{ $post->translated_excerpt ?? $post->excerpt }}
                            </p>
                        @elseif ($post->translated_tldr ?? $post->tldr)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3 flex-grow italic">
                                <span class="font-semibold">TL;DR:</span> {{ $post->translated_tldr ?? $post->tldr }}
                            </p>
                        @endif

                        <!-- Author Info -->
                        @if(config('blogr.display.show_author_pseudo') || config('blogr.display.show_author_avatar'))
                            <div class="mb-4">
                                <x-blogr::author-info :author="$post->user" size="sm" />
                            </div>
                        @endif

                        <!-- Tags -->
                        @if ($post->tags->count())
                            <div class="mb-4 flex flex-wrap gap-2">
                                @foreach ($post->tags->take(3) as $tag)
                                    @php
                                        $tagTranslation = $tag->translate($currentLocale);
                                        $tagName = $tagTranslation ? $tagTranslation->name : $tag->name;
                                        $tagSlug = $tagTranslation ? $tagTranslation->slug : $tag->slug;
                                    @endphp
                                    <a href="{{ config('blogr.locales.enabled') ? route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tagSlug]) : route('blog.tag', ['tagSlug' => $tagSlug]) }}"
                                       class="inline-block bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs px-2.5 py-1 rounded-full hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                        #{{ $tagName }}
                                    </a>
                                @endforeach
                                @if($post->tags->count() > 3)
                                    <span class="inline-block text-blue-600 dark:text-blue-400 text-xs px-2.5 py-1">
                                        +{{ $post->tags->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Read More Button -->
                        <a href="{{ config('blogr.locales.enabled') ? route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug]) : route('blog.show', ['slug' => $post->translated_slug]) }}" 
                           class="mt-auto inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold group/link">
                            {{ __('blogr::blogr.ui.read_more') }}
                            <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-20">
                    <svg class="w-24 h-24 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-xl">{{ __('blogr::blogr.ui.no_posts_yet') }}</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">{{ __('blogr::blogr.ui.check_back_soon') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        @if($posts->hasPages())
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
