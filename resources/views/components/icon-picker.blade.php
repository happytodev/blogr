@php
    $statePath = $getStatePath();
    $iconsData = $getIconsWithSvg();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="iconPicker({
            state: $wire.$entangle(@js($statePath)),
            icons: @js($iconsData),
        })"
        wire:ignore.self
        wire:key="{{ $getLivewireKey() }}.icon-picker"
        class="space-y-3"
    >
        {{-- Preview + search + clear row --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                <template x-if="selectedIcon">
                    <div x-html="previewSvg" class="w-6 h-6 text-gray-700 dark:text-gray-300"></div>
                </template>
            </div>

            <button
                type="button"
                x-show="selectedIcon"
                x-on:click="clearIcon()"
                class="flex items-center justify-center w-6 h-6 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all flex-shrink-0"
                title="Remove icon"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

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
                <template x-for="(icon, key) in filteredIcons" :key="key">
                    <button
                        type="button"
                        x-on:click="selectIcon(icon.name)"
                        class="flex items-center justify-center w-10 h-10 rounded-lg border border-transparent hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all"
                        :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': icon.name === selectedIcon }"
                        :title="icon.name"
                        x-html="icon.svg"
                    >
                    </button>
                </template>
            </div>
            <p x-show="filteredIcons.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                No icons found.
            </p>
        </div>
    </div>
</x-dynamic-component>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('iconPicker', (config) => ({
            selectedIcon: config.state,
            allIcons: config.icons ?? [],
            search: '',
            previewSvg: '',

            init() {
                this.updatePreview();
                this.$watch('selectedIcon', () => this.updatePreview());
            },

            updatePreview() {
                if (this.selectedIcon) {
                    const found = this.allIcons.find(i => i.name === this.selectedIcon);
                    this.previewSvg = found ? found.svg : '';
                } else {
                    this.previewSvg = '';
                }
            },

            get filteredIcons() {
                if (!this.search || this.search.length === 0) return [];
                const lower = this.search.toLowerCase();
                return this.allIcons.filter(icon => icon.name.includes(lower)).slice(0, 80);
            },

            selectIcon(name) {
                this.selectedIcon = name;
                this.search = '';
            },

            clearIcon() {
                this.selectedIcon = null;
                this.search = '';
            },
        }));
    });
</script>
@endpush
