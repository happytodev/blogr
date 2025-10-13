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
            <h1 class="text-5xl font-bold mb-4 text-gray-900 dark:text-white">Blog Series</h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Browse all our blog series and learn step by step</p>
        </div>

        <!-- Series Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($series as $s)
                <div class="group bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 border-2 border-blue-200 dark:border-blue-800 flex flex-col">
                    <a href="{{ route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $s->slug]) }}" class="flex-grow flex flex-col">
                        <!-- Series Image -->
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ $s->photo_url }}" 
                                 alt="{{ $s->translated_title ?? $s->title }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            
                            <!-- Posts Count Badge -->
                            <div class="absolute bottom-4 left-4 right-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    {{ $s->posts->count() }} articles
                                </span>
                            </div>

                            <!-- Featured Badge -->
                            @if($s->is_featured)
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500 text-white shadow-lg">
                                    ⭐ Featured
                                </span>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Series Content -->
                        <div class="p-6 flex-grow flex flex-col">
                            <h2 class="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ $s->translated_title ?? $s->title }}
                            </h2>
                            
                            @if($s->translated_description ?? $s->description)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3 flex-grow">
                                {{ $s->translated_description ?? $s->description }}
                            </p>
                            @endif
                            
                            <!-- Series Authors -->
                            @if(config('blogr.display.show_series_authors'))
                                @php
                                    $seriesAuthors = $s->authors();
                                @endphp
                                @if(count($seriesAuthors) > 0)
                                <div class="mb-4">
                                    <x-blogr::series-authors :authors="$seriesAuthors" size="sm" />
                                </div>
                                @endif
                            @endif
                            
                            <!-- Published Date -->
                            @if($s->published_at)
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mt-auto pt-4 border-t border-gray-200 dark:border-gray-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $s->published_at->locale($currentLocale ?? app()->getLocale())->isoFormat('LL') }}
                            </div>
                            @endif
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">No series published yet</p>
                </div>
            @endforelse
        </div>

        <!-- Back to blog link -->
        <div class="mt-12 text-center">
            <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" 
               class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to blog
            </a>
        </div>
    </div>
@endsection
