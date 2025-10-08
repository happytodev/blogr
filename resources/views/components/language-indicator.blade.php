@props(['translations', 'currentLocale' => config('blogr.locales.default', 'en')])<div {{ $attributes }}>

    {{ $slot}}

@if(config('blogr.posts.show_language_indicator', true) && config('blogr.locales.enabled', false) && $translations && count($translations) > 1)</div>

<div class="flex items-center space-x-2 text-sm">
    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
    </svg>
    <span class="text-gray-600 dark:text-gray-400">Available in:</span>
    <div class="flex flex-wrap gap-2">
        @foreach($translations as $translation)
            @php
                $isCurrent = $translation['locale'] === $currentLocale;
                $localeNames = [
                    'en' => 'English',
                    'fr' => 'Français',
                    'es' => 'Español',
                    'de' => 'Deutsch',
                    'it' => 'Italiano',
                    'pt' => 'Português',
                ];
                $localeName = $localeNames[$translation['locale']] ?? strtoupper($translation['locale']);
            @endphp
            
            @if($isCurrent)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                    {{ strtoupper($translation['locale']) }}
                </span>
            @else
                <a href="{{ $translation['url'] }}" 
                   class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                   title="{{ $localeName }}">
                    {{ strtoupper($translation['locale']) }}
                </a>
            @endif
        @endforeach
    </div>
</div>
@endif
