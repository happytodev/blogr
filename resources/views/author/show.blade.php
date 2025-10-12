@extends('blogr::layouts.blog')

@section('content')
    <div class="container mx-auto px-4 py-12 max-w-6xl">
        {{-- Author Profile Header --}}
        <div class="mb-12">
        <div class="flex items-center gap-6 mb-6">
            {{-- Author Avatar --}}
            @if($author->avatar ?? false)
                <img src="{{ url('storage/' . $author->avatar) }}" 
                     alt="{{ $author->name }}" 
                     class="w-24 h-24 rounded-full object-cover ring-4 ring-gray-200 dark:ring-gray-700">
            @else
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-4 ring-gray-200 dark:ring-gray-700">
                    <span class="text-3xl font-bold text-white">
                        {{ strtoupper(substr($author->name, 0, 1)) }}
                    </span>
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $author->name }}
                </h1>
                
                @if($author->email ?? false)
                    <p class="text-gray-600 dark:text-gray-400 mb-2">
                        <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ $author->email }}
                    </p>
                @endif

                @if($author->bio ?? false)
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        @if(is_array($author->bio))
                            {{ $author->bio[$currentLocale] ?? $author->bio[config('blogr.locales.default', 'en')] ?? '' }}
                        @else
                            {{ $author->bio }}
                        @endif
                    </p>
                @endif
            </div>
        </div>

        {{-- Author Stats --}}
        <div class="flex gap-6 text-sm">
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-semibold">{{ $posts->total() }}</span> 
                <span>{{ $posts->total() === 1 ? __('post') : __('posts') }}</span>
            </div>
        </div>
    </div>

    {{-- Author's Posts --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
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
            <div class="grid gap-8">
                @foreach($posts as $post)
                    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                        <div class="flex flex-col md:flex-row">
                            {{-- Post Image --}}
                            @if($post->image)
                                <div class="md:w-1/3">
                                    <img src="{{ Storage::url($post->image) }}" 
                                         alt="{{ $post->title }}" 
                                         class="w-full h-48 md:h-full object-cover">
                                </div>
                            @endif

                            {{-- Post Content --}}
                            <div class="flex-1 p-6">
                                <div class="flex items-center gap-4 mb-3 text-sm text-gray-600 dark:text-gray-400">
                                    <time datetime="{{ $post->published_at->format('Y-m-d') }}">
                                        {{ $post->published_at->format('M d, Y') }}
                                    </time>
                                    
                                    @if($post->category)
                                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                                            {{ $post->category->name }}
                                        </span>
                                    @endif

                                    @if($post->reading_time)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $post->reading_time }} min
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->slug]) }}">
                                        {{ $post->title }}
                                    </a>
                                </h3>

                                @if($post->tldr)
                                    <p class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                                        {{ $post->tldr }}
                                    </p>
                                @endif

                                <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $post->slug]) }}" 
                                   class="inline-flex items-center text-blue-600 dark:text-blue-400 font-semibold hover:text-blue-800 dark:hover:text-blue-300">
                                    {{ __('Read more') }}
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
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
