@props(['author', 'showAvatar' => null, 'showPseudo' => null, 'size' => 'sm'])

@if($author)
@php
    $showAvatar = $showAvatar ?? config('blogr.display.show_author_avatar', true);
    $showPseudo = $showPseudo ?? config('blogr.display.show_author_pseudo', true);
    $displayName = $showPseudo && $author->slug ? $author->slug : $author->name;
    $authorProfileEnabled = config('blogr.author_profile.enabled', true) && isset($author->slug) && !empty($author->slug);
    $localesEnabled = config('blogr.locales.enabled', false);
    
    // Build route parameters if author profile is enabled
    if ($authorProfileEnabled) {
        $currentLocale = app()->getLocale();
        if ($localesEnabled) {
            $routeParams = ['locale' => $currentLocale, 'userSlug' => $author->slug];
        } else {
            $routeParams = ['userSlug' => $author->slug];
        }
    }
    
    // Size classes
    $sizeClasses = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
    ];
    $avatarClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

@if($authorProfileEnabled)
    <a href="{{ route('blog.author', $routeParams) }}" {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        @if($showAvatar && $author->avatar)
            <img 
                src="{{ Storage::disk('public')->url($author->avatar) }}" 
                alt="{{ $displayName }}"
                class="{{ $avatarClass }} rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 hover:ring-[var(--color-primary)] dark:hover:ring-[var(--color-primary-dark)] transition-all duration-200"
            />
        @elseif($showAvatar)
            <div class="{{ $avatarClass }} rounded-full bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-primary-dark)] flex items-center justify-center font-semibold text-white ring-2 ring-gray-200 dark:ring-gray-700 hover:ring-[var(--color-primary)] dark:hover:ring-[var(--color-primary-dark)] transition-all duration-200">
                {{ strtoupper(substr($displayName, 0, 1)) }}
            </div>
        @endif
        
        {{-- Display name with tooltip showing full text --}}
        <span class="text-gray-700 dark:text-gray-300 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] truncate max-w-[10rem] transition-colors" title="{{ $displayName }}">{{ $displayName }}</span>
    </a>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        @if($showAvatar && $author->avatar)
            <img 
                src="{{ Storage::disk('public')->url($author->avatar) }}" 
                alt="{{ $displayName }}"
                class="{{ $avatarClass }} rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
            />
        @elseif($showAvatar)
            <div class="{{ $avatarClass }} rounded-full bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-primary-dark)] flex items-center justify-center font-semibold text-white ring-2 ring-gray-200 dark:ring-gray-700">
                {{ strtoupper(substr($displayName, 0, 1)) }}
            </div>
        @endif
        
        <span class="text-gray-700 dark:text-gray-300 truncate max-w-[10rem]" title="{{ $displayName }}">{{ $displayName }}</span>
    </div>
@endif
@endif
