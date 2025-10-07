@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-5xl font-bold mb-4">Blog Series</h1>
        <p class="text-xl text-gray-600 mb-8">Browse all our blog series and learn step by step</p>

        <div class="grid gap-6 md:gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($series as $s)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col">
                    <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $s->slug]) }}" class="flex-grow flex flex-col">
                        <div class="h-48 overflow-hidden bg-gradient-to-br from-purple-500 to-purple-700">
                            <img src="{{ $s->photo_url }}" alt="{{ $s->title }}" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="p-6 flex flex-col flex-grow">
                            <div class="flex items-start justify-between mb-2">
                                <h2 class="text-2xl font-bold text-gray-900 flex-grow">{{ $s->title }}</h2>
                                @if($s->is_featured)
                                    <span class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⭐ Featured
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-gray-600 mb-4 flex-grow">{{ $s->description }}</p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mt-auto pt-4 border-t border-gray-100">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    {{ $s->posts->count() }} {{ $s->posts->count() === 1 ? 'post' : 'posts' }}
                                </span>
                                @if($s->published_at)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $s->published_at->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-gray-600 text-lg">No series published yet</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" class="text-purple-600 hover:text-purple-800 hover:underline inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to blog
            </a>
        </div>
    </div>
@endsection
