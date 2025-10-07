@props(['currentLocale' => config('blogr.locales.default', 'en'), 'availableLocales' => config('blogr.locales.available', ['en'])])

<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 {{ config('blogr.ui.navigation.sticky', true) ? 'sticky top-0 z-50' : '' }} transition-colors duration-200">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo / Site Name -->
            @if(config('blogr.ui.navigation.show_logo', true))
            <div class="flex-shrink-0">
                <a href="{{ route('blog.index', ['locale' => $currentLocale]) }}" class="text-2xl font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    {{ config('blogr.seo.site_name', config('app.name', 'Blog')) }}
                </a>
            </div>
            @endif

            <!-- Right Side: Language Switcher & Theme Switcher -->
            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                @if(config('blogr.ui.navigation.show_language_switcher', true) && config('blogr.locales.enabled', false) && count($availableLocales) > 1)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" 
                            class="flex items-center space-x-1 px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span class="uppercase">{{ $currentLocale }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                        <div class="py-1" role="menu">
                            @foreach($availableLocales as $locale)
                            @php
                                // Get current route and replace locale parameter
                                $currentRouteName = request()->route()->getName();
                                $currentParams = request()->route()->parameters();
                                $currentParams['locale'] = $locale;
                            @endphp
                            <a href="{{ route($currentRouteName, $currentParams) }}" 
                               class="block px-4 py-2 text-sm {{ $locale === $currentLocale ? 'bg-gray-100 dark:bg-gray-700 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors"
                               role="menuitem">
                                <span class="uppercase font-semibold">{{ $locale }}</span>
                                <span class="ml-2 text-xs">
                                    @switch($locale)
                                        @case('en') English @break
                                        @case('fr') Français @break
                                        @case('es') Español @break
                                        @case('de') Deutsch @break
                                        @default {{ strtoupper($locale) }}
                                    @endswitch
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Theme Switcher -->
                @if(config('blogr.ui.navigation.show_theme_switcher', true))
                <div x-data="themeSwitch()" class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                    <button @click="setTheme('light')" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow': theme === 'light' }"
                            class="p-2 rounded-md transition-all duration-200"
                            title="Light mode">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button @click="setTheme('auto')" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow': theme === 'auto' }"
                            class="p-2 rounded-md transition-all duration-200"
                            title="Auto mode">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button @click="setTheme('dark')" 
                            :class="{ 'bg-white dark:bg-gray-700 shadow': theme === 'dark' }"
                            class="p-2 rounded-md transition-all duration-200"
                            title="Dark mode">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('themeSwitch', () => ({
            theme: localStorage.getItem('theme') || '{{ config('blogr.ui.theme.default', 'light') }}',
            
            init() {
                this.applyTheme();
                
                // Watch for system theme changes
                if (this.theme === 'auto') {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                        if (this.theme === 'auto') {
                            this.applyTheme();
                        }
                    });
                }
            },
            
            setTheme(newTheme) {
                this.theme = newTheme;
                localStorage.setItem('theme', newTheme);
                this.applyTheme();
            },
            
            applyTheme() {
                if (this.theme === 'dark' || (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }));
    });
</script>
@endpush
