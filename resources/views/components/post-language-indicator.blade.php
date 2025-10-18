@if(config('blogr.posts.show_language_switcher', true) && count($translations) > 1)
<div class="inline-flex items-center space-x-2 bg-[var(--color-primary)]/10 dark:bg-[var(--color-primary-dark)]/20 px-4 py-2 rounded-lg border border-[var(--color-primary)]/30 dark:border-[var(--color-primary-dark)]/50">
    <svg class="w-5 h-5 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
    </svg>
    <span class="text-sm font-medium text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">Available in:</span>
    <div class="flex items-center space-x-2">
        @foreach($translations as $translation)
        @php
            $localeNames = [
                'en' => 'English',
                'fr' => 'Français',
                'es' => 'Español',
                'de' => 'Deutsch',
            ];
            $localeName = $localeNames[$translation['locale']] ?? strtoupper($translation['locale']);
        @endphp
        
        @if($translation['locale'] === $currentLocale)
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-[var(--color-primary)] dark:bg-[var(--color-primary-dark)] text-white">
                {{ strtoupper($translation['locale']) }}
            </span>
        @else
            <a href="{{ $translation['url'] }}" 
               class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white dark:bg-gray-800 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:bg-[var(--color-primary)]/20 dark:hover:bg-[var(--color-primary-dark)]/30 border border-[var(--color-primary)]/30 dark:border-[var(--color-primary-dark)]/50 transition-colors"
               title="{{ $localeName }}">
                {{ strtoupper($translation['locale']) }}
            </a>
        @endif
        @endforeach
    </div>
</div>
@endif
