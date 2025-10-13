@props(['currentLocale', 'translationLocale', 'availableLocales' => []])<div {{ $attributes }}>

    {{ $slot}}

@if($currentLocale !== $translationLocale)</div>

<div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-400 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                {{ __('blogr::blogr.ui.translation_unavailable_title') }}
            </h3>
            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                <p>{{ __('blogr::blogr.ui.translation_unavailable_message', [
                    'requested' => strtoupper($currentLocale),
                    'showing' => strtoupper($translationLocale)
                ]) }}</p>
            </div>
            
            @if(count($availableLocales) > 0)
            <div class="mt-3">
                <p class="text-xs text-yellow-700 dark:text-yellow-400 mb-2">
                    {{ __('blogr::blogr.ui.translation_available_in') }}:
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableLocales as $locale)
                        <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => request()->route('slug')]) }}" 
                           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 hover:bg-yellow-300 dark:hover:bg-yellow-700 transition-colors">
                            {{ strtoupper($locale) }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
