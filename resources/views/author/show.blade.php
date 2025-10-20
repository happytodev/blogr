@extends('blogr::layouts.blog')

@section('content')
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        {{-- Author Profile Header --}}
        <div class="mb-12">
            {{-- Author Avatar and Name --}}
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6 mb-6">
                {{-- Author Avatar --}}
                @php $showAvatar = config('blogr.display.show_author_avatar', true); @endphp
                @if($showAvatar && ($author->avatar ?? false))
                    <img src="{{ url('storage/' . $author->avatar) }}" 
                         alt="{{ $author->name }}" 
                         class="w-24 h-24 rounded-full object-cover ring-4 ring-white dark:ring-gray-600 shadow-lg flex-shrink-0">
                @elseif($showAvatar)
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-primary-dark)] flex items-center justify-center ring-4 ring-white dark:ring-gray-600 shadow-lg flex-shrink-0">
                        <span class="text-3xl font-bold text-white">
                            {{ strtoupper(substr($author->name, 0, 1)) }}
                        </span>
                    </div>
                @endif

                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3">
                        {{ $author->name }}
                    </h1>

                    {{-- Author Stats --}}
                    <div class="flex gap-6 text-sm justify-center md:justify-start">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-semibold">{{ $posts->total() }}</span> 
                            <span>{{ $posts->total() === 1 ? __('post') : __('posts') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Author Bio with same styling as article pages --}}
            @if(!empty($bioHtml))
                <div class="bg-[var(--color-primary)]/10 dark:bg-[var(--color-primary-dark)]/20 border-l-4 border-[var(--color-primary)] dark:border-[var(--color-primary-dark)] p-6 rounded-r-xl">
                    <p class="text-sm font-semibold dark:text-white text-gray-900 uppercase tracking-wide mb-3">
                        {{ __('blogr::blogr.ui.about_the_author') }}
                    </p>
                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                        {!! $bioHtml !!}
                    </div>
                </div>
            @endif
        </div>

    {{-- Author's Posts --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
            {{ __('Articles by') }} {{ $author->name }}
        </h2>

        @if($posts->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                    {{ __('This author has not published any posts yet.') }}
                </p>
            </div>
        @else
            {{-- Posts Grid with consistent card layout --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 auto-rows-fr">
                @foreach($posts as $post)
                    <article class="group bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 flex flex-col h-full">
                        {{-- Post Image --}}
                        <div class="relative h-56 bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden">
                            <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug]) }}" class="block h-full">
                                @if($post->photo_url ?? false)
                                    <img src="{{ $post->photo_url }}" 
                                         alt="{{ $post->translated_title }}" 
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <img src="{{ asset(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg')) }}" 
                                         alt="{{ $post->translated_title }}"
                                         class="absolute inset-0 w-full h-full object-cover opacity-50">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </a>
                            
                            {{-- Category Badge --}}
                            <div class="absolute top-4 left-4">
                                @if($post->category)
                                    <a href="{{ route('blog.category', ['locale' => $currentLocale, 'categorySlug' => $post->category->slug]) }}"
                                       class="inline-block bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold text-gray-900 dark:text-white hover:bg-white dark:hover:bg-gray-900 transition-colors">
                                        {{ $post->category->name }}
                                    </a>
                                @endif
                            </div>

                            {{-- Reading Time Badge --}}
                            @if(config('blogr.reading_time.enabled', true) && $post->reading_time)
                                <div class="absolute top-4 right-4 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium text-white flex items-center">
                                    @include('blogr::components.clock-icon')
                                    <span class="ml-1">{{ \Happytodev\Blogr\Helpers\ConfigHelper::getReadingTimeText($post->reading_time) }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Post Content --}}
                        <div class="p-6 flex-grow flex flex-col">
                            {{-- Title --}}
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                                <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug]) }}"
                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $post->translated_title }}
                                </a>
                            </h3>

                            {{-- TLDR/Excerpt --}}
                            @if($post->translated_tldr ?? false)
                                <p class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                                    {{ $post->translated_tldr }}
                                </p>
                            @endif

                            {{-- Bottom Section: Tags + Published Date + Read More (always at bottom) --}}
                            <div class="mt-auto space-y-4">
                                {{-- Tags --}}
                                @if($post->tags && $post->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($post->tags as $tag)
                                            <a href="{{ route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tag->slug]) }}"
                                               class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                #{{ $tag->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Published Date + Read More --}}
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                    {{-- Published Date --}}
                                    @if($post->published_at && config('blogr.ui.dates.show_publication_date', true) && config('blogr.ui.dates.show_publication_date_on_cards', true))
                                        @php
                                            // Set Carbon locale for date formatting
                                            $carbonDate = $post->published_at->copy()->locale($currentLocale);
                                        @endphp
                                        <time datetime="{{ $post->published_at->toIso8601String() }}" class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            {{ $carbonDate->isoFormat('LL') }}
                                        </time>
                                    @else
                                        <div></div>
                                    @endif
                                    
                                    {{-- Read More Link --}}
                                    <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->translated_slug]) }}" 
                                       class="text-blue-600 dark:text-blue-400 font-semibold hover:text-blue-800 dark:hover:text-blue-300 text-sm transition-colors">
                                        {{ __('Read more') }} →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
