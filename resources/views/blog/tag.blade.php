@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@section('content')
    <div class="container mx-auto px-4 py-12">
        <!-- Page Header -->
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-bold mb-4 text-gray-900 dark:text-white">Posts with tag {{ $displayName ?? $tag->name }}</h1>
            <a href="{{ route('blog.index', $currentLocale) }}" 
               class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to blog
            </a>
        </div>

        <!-- Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($posts as $post)
                @php
                    $postTranslation = $post->translations->first();
                    $postSlug = $postTranslation ? $postTranslation->slug : $post->slug;
                    $postTitle = $postTranslation ? $postTranslation->title : $post->title;
                    $postExcerpt = $postTranslation ? $postTranslation->excerpt : $post->excerpt;
                    $postTldr = $postTranslation ? $postTranslation->tldr : $post->tldr;
                @endphp
                <article class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                    <!-- Post Image -->
                    <div class="relative h-56 bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden">
                        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $postSlug]) }}" class="block h-full">
                            @if ($post->photo)
                                <img src="{{ $post->photo_url }}" 
                                     alt="{{ $postTitle }}"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <img src="{{ asset(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg')) }}" 
                                     alt="{{ $postTitle }}"
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
                            @php
                                $categoryTranslation = $post->category->translate($currentLocale);
                                $categoryName = $categoryTranslation ? $categoryTranslation->name : $post->category->name;
                                $categorySlug = $categoryTranslation ? $categoryTranslation->slug : $post->category->slug;
                            @endphp
                            <a href="{{ route('blog.category', ['locale' => $currentLocale, 'categorySlug' => $categorySlug]) }}"
                               class="inline-block bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold text-gray-900 dark:text-white hover:bg-white dark:hover:bg-gray-900 transition-colors">
                                {{ $categoryName }}
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
                            <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $postSlug]) }}">
                                {{ $postTitle }}
                            </a>
                        </h2>

                        <!-- Excerpt or TL;DR -->
                        @if ($postExcerpt)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3 flex-grow">
                                {{ $postExcerpt }}
                            </p>
                        @elseif ($postTldr)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3 flex-grow italic">
                                <span class="font-semibold">TL;DR:</span> {{ $postTldr }}
                            </p>
                        @endif

                        <!-- Tags -->
                        @if ($post->tags->count())
                            <div class="mb-4 flex flex-wrap gap-2">
                                @foreach ($post->tags->take(3) as $postTag)
                                    @php
                                        $tagTranslation = $postTag->translate($currentLocale);
                                        $tagName = $tagTranslation ? $tagTranslation->name : $postTag->name;
                                        $tagSlug = $tagTranslation ? $tagTranslation->slug : $postTag->slug;
                                    @endphp
                                    <a href="{{ route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tagSlug]) }}"
                                       class="inline-block bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-xs px-2.5 py-1 rounded-full hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                        #{{ $tagName }}
                                    </a>
                                @endforeach
                                @if($post->tags->count() > 3)
                                    <span class="inline-block text-gray-500 dark:text-gray-400 text-xs px-2.5 py-1">
                                        +{{ $post->tags->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Read More Link -->
                        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $postSlug]) }}" 
                           class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold text-sm group/link mt-auto">
                            Read more
                            <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection