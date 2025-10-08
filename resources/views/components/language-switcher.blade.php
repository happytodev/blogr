@props(['currentRoute', 'routeParameters' => []])

@php
    use Happytodev\Blogr\Helpers\LocaleHelper;
    
    $currentLocale = LocaleHelper::currentLocale();
    $availableLocales = LocaleHelper::availableLocales();
    $alternateUrls = LocaleHelper::alternateUrls($currentRoute, $routeParameters);
    
    $localeNames = [
        'en' => 'English',
        'fr' => 'Français',
        'es' => 'Español',
        'de' => 'Deutsch',
    ];
@endphp

@if(count($availableLocales) > 1)
<div class="language-switcher" {{ $attributes }}>
    <div class="relative inline-block text-left">
        <button type="button" 
                class="inline-flex items-center justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500"
                id="language-menu-button"
                aria-expanded="false"
                aria-haspopup="true"
                onclick="document.getElementById('language-menu').classList.toggle('hidden')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
            </svg>
            {{ $localeNames[$currentLocale] ?? strtoupper($currentLocale) }}
            <svg class="-mr-1 ml-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
             id="language-menu"
             role="menu"
             aria-orientation="vertical"
             aria-labelledby="language-menu-button">
            <div class="py-1" role="none">
                @foreach($availableLocales as $locale)
                    <a href="{{ $alternateUrls[$locale] }}" 
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ $locale === $currentLocale ? 'bg-gray-50 font-semibold' : '' }}"
                       role="menuitem">
                        <span class="inline-block w-6 text-center mr-2">
                            @if($locale === $currentLocale)
                                ✓
                            @endif
                        </span>
                        {{ $localeNames[$locale] ?? strtoupper($locale) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('language-menu');
        const button = document.getElementById('language-menu-button');
        
        if (!button.contains(event.target) && !menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>
@endif
