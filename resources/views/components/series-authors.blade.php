@props(['authors' => [], 'limit' => null, 'size' => 'md'])

@php
    // Get limit from config if not provided
    $limit = $limit ?? config('blogr.display.series_authors_limit', 4);
    $showSeriesAuthors = config('blogr.display.show_series_authors', true);
    
    // Convert to array if it's a collection
    $authorsArray = is_array($authors) ? $authors : (method_exists($authors, 'toArray') ? $authors->toArray() : []);
    
    $visibleAuthors = array_slice($authorsArray, 0, $limit);
    $remainingCount = max(0, count($authorsArray) - $limit);
    
    // Size configurations for avatars
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
    ];
    
    $avatarSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

@if($showSeriesAuthors && count($authorsArray) > 0)
<!-- Series Authors -->
<div {{ $attributes->merge(['class' => 'flex items-center']) }}>
    <div class="flex -space-x-2">
        @foreach($visibleAuthors as $author)
            @php
                // Handle both array and object formats
                $authorId = is_array($author) ? ($author['id'] ?? null) : ($author->id ?? null);
                $authorName = is_array($author) ? ($author['name'] ?? 'Unknown') : ($author->name ?? 'Unknown');
                $authorSlug = is_array($author) ? ($author['slug'] ?? null) : ($author->slug ?? null);
                $authorAvatar = is_array($author) ? ($author['avatar'] ?? null) : ($author->avatar ?? null);
                
                // Get initials for fallback
                $initials = collect(explode(' ', $authorName))
                    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                    ->take(2)
                    ->join('');
                
                // Build author profile URL
                $authorUrl = null;
                if (config('blogr.author_profile.enabled') && $authorSlug) {
                    $locale = config('blogr.locales.enabled') ? app()->getLocale() : null;
                    $prefix = config('blogr.route.prefix', 'blog');
                    $homepage = config('blogr.route.homepage', false);
                    
                    if ($locale && !$homepage) {
                        $authorUrl = "/{$locale}/{$prefix}/author/{$authorSlug}";
                    } elseif ($homepage) {
                        $authorUrl = "/author/{$authorSlug}";
                    } else {
                        $authorUrl = "/{$prefix}/author/{$authorSlug}";
                    }
                }
            @endphp
            
            <div class="relative group">
                @if($authorUrl)
                    <a href="{{ $authorUrl }}" 
                       class="block {{ $avatarSize }} rounded-full border-2 border-white dark:border-gray-800 overflow-hidden ring-2 ring-gray-200 dark:ring-gray-700 hover:ring-blue-500 dark:hover:ring-blue-400 transition-all duration-200 hover:scale-110 hover:z-10"
                       title="{{ $authorSlug ?? $authorName }}">
                @else
                    <div class="{{ $avatarSize }} rounded-full border-2 border-white dark:border-gray-800 overflow-hidden ring-2 ring-gray-200 dark:ring-gray-700">
                @endif
                    
                    @if($authorAvatar)
                        <img src="{{ Storage::url($authorAvatar) }}" 
                             alt="{{ $authorName }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 text-white font-semibold">
                            {{ $initials }}
                        </div>
                    @endif
                    
                @if($authorUrl)
                    </a>
                @else
                    </div>
                @endif
                
                {{-- Tooltip --}}
                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-20 shadow-lg">
                    {{ $authorSlug ?? $authorName }}
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                        <div class="border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                    </div>
                </div>
            </div>
        @endforeach
        
        @if($remainingCount > 0)
            <div class="{{ $avatarSize }} rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold ring-2 ring-gray-200 dark:ring-gray-700">
                +{{ $remainingCount }}
            </div>
        @endif
    </div>
</div>
@endif
