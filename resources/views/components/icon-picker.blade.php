@php
    use Happytodev\Blogr\Helpers\IconHelper;
    use function Filament\Support\generate_icon_html;

    $statePath = $getStatePath();
    $icons = $getIcons();
    $selectedIcon = $getState();
    $prefix = $getIconsPrefix();
    $allIconsJson = json_encode(array_values($icons));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="iconPicker({
            statePath: @js($statePath),
            selectedIcon: @js($selectedIcon),
            allIcons: @js(array_values($icons)),
        })"
        class="space-y-3"
    >
        {{-- Preview + search row --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <template x-if="selectedIcon && selectedIcon.length > 0">
                    <div x-html="previewSvg" class="w-6 h-6 text-gray-700 dark:text-gray-300"></div>
                </template>
            </div>
            <div class="flex-1">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        x-model="search"
                        x-ref="searchInput"
                        placeholder="Search icons…"
                        @keydown.escape="search = ''; $refs.searchInput.blur()"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        {{-- Grid --}}
        <div
            x-show="search.length > 0"
            x-collapse.duration.200ms
            class="border border-gray-200 dark:border-gray-700 rounded-lg p-2 max-h-64 overflow-y-auto"
        >
            <div class="grid grid-cols-8 gap-1">
                <template x-for="(name, key) in filteredIcons" :key="key">
                    <button
                        type="button"
                        x-on:click="selectIcon(name)"
                        class="flex items-center justify-center w-10 h-10 rounded-lg border border-transparent hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all"
                        :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': name === selectedIcon }"
                        :title="name"
                    >
                        <span x-text="name.substring(0, 2)" class="text-xs text-gray-400 dark:text-gray-500"></span>
                    </button>
                </template>
            </div>
            <p x-show="filteredIcons.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                No icons found.
            </p>
        </div>

        {{-- Hidden input for Livewire --}}
        <input type="hidden" :value="selectedIcon" :name="statePath" />
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('iconPicker', (config) => ({
                statePath: config.statePath,
                selectedIcon: config.selectedIcon || '',
                allIcons: config.allIcons ?? [],
                search: '',
                previewSvg: '',

                init() {
                    this.$watch('search', (value) => {
                        // search is used in the template for filteredIcons
                    });
                    this.$watch('selectedIcon', (value) => {
                        if (value) {
                            fetch('/blogr/icon-svg/' + value)
                                .then(r => r.text())
                                .then(h => { this.previewSvg = h; })
                                .catch(() => { this.previewSvg = ''; });
                        } else {
                            this.previewSvg = '';
                        }
                    });
                    // initial preview
                    if (this.selectedIcon) {
                        fetch('/blogr/icon-svg/' + this.selectedIcon)
                            .then(r => r.text())
                            .then(h => { this.previewSvg = h; })
                            .catch(() => {});
                    }
                },

                get filteredIcons() {
                    if (!this.search || this.search.length === 0) return [];
                    const lower = this.search.toLowerCase();
                    return this.allIcons.filter(name => name.includes(lower)).slice(0, 80);
                },

                selectIcon(name) {
                    this.selectedIcon = name;
                    this.search = '';
                    this.$wire.set(this.statePath, name);
                },
            }));
        });
    </script>
</x-dynamic-component>
