@props(['author', 'compact' => false, 'locale' => null])

@php
    // Check if author exists
    if (!$author) {
        return;
    }
    
    $locale = $locale ?? app()->getLocale();
    $localesEnabled = config('blogr.locales.enabled', false);
    $authorProfileEnabled = config('blogr.author_profile.enabled', true) && isset($author->slug) && !empty($author->slug);
    
    // Build route parameters based on whether locales are enabled (only if slug exists)
    if ($authorProfileEnabled) {
        if ($localesEnabled) {
            $routeParams = ['locale' => $locale, 'userSlug' => $author->slug];
        } else {
            $routeParams = ['userSlug' => $author->slug];
        }
    }
@endphp

@if($compact)
    {{-- Compact version (inline with post content) --}}
    <div {{ $attributes->merge(['class' => 'flex items-center gap-4 py-4 border-t border-b border-gray-200 dark:border-gray-700']) }}>
        @if($author->avatar ?? false)
            <img src="{{ url('storage/' . $author->avatar) }}" 
                 alt="{{ $author->name }}" 
                 class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700">
        @else
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-2 ring-gray-200 dark:ring-gray-700">
                <span class="text-lg font-bold text-white">
                    {{ strtoupper(substr($author->name, 0, 1)) }}
                </span>
            </div>
        @endif

        <div class="flex-1">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Written by') }}</p>
            @if($authorProfileEnabled)
                <a href="{{ route('blog.author', $routeParams) }}" 
                   class="text-lg font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    {{ $author->name }}
                </a>
            @else
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $author->name }}
                </p>
            @endif
        </div>
    </div>
@else
    {{-- Full version (author bio box) --}}
    <div {{ $attributes->merge(['class' => 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 my-8']) }}>
        <div class="flex gap-6">
            {{-- Author Avatar --}}
            @if($author->avatar ?? false)
                <img src="{{ url('storage/' . $author->avatar) }}" 
                     alt="{{ $author->name }}" 
                     class="w-20 h-20 rounded-full object-cover ring-4 ring-white dark:ring-gray-600 shadow-lg flex-shrink-0">
            @else
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-4 ring-white dark:ring-gray-600 shadow-lg flex-shrink-0">
                    <span class="text-2xl font-bold text-white">
                        {{ strtoupper(substr($author->name, 0, 1)) }}
                    </span>
                </div>
            @endif

            {{-- Author Info --}}
            <div class="flex-1">
                <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-2">
                    {{ __('About the author') }}
                </p>
                
                @if($authorProfileEnabled)
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        <a href="{{ route('blog.author', $routeParams) }}" 
                           class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            {{ $author->name }}
                        </a>
                    </h3>
                @else
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                        {{ $author->name }}
                    </h3>
                @endif

                @if($author->bio ?? false)
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        @if(is_array($author->bio))
                            {{ $author->bio[$locale] ?? $author->bio[config('blogr.locales.default', 'en')] ?? '' }}
                        @else
                            {{ $author->bio }}
                        @endif
                    </p>
                @endif

                <div class="flex items-center gap-4 text-sm">
                    @if($authorProfileEnabled)
                        <a href="{{ route('blog.author', $routeParams) }}" 
                           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-100 dark:hover:bg-gray-500 transition-colors shadow-sm">
                            {{ __('View all posts') }}
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endif

                    @if($author->email ?? false)
                        <a href="mailto:{{ $author->email }}" 
                           class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
