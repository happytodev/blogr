<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6">
            {{ $this->form }}
        </div>

        <div class="flex justify-end !pt-16 !mt-16 border-t-2 border-gray-200 dark:border-gray-700" style="margin-top: 16px;">
            <x-filament::button
                type="submit"
                color="primary"
            >
                {{ __('blogr::blogr.settings.save') }}
            </x-filament::button>
        </div>
    </form>

    @php
        $presetsJson = json_encode(\Happytodev\Blogr\Filament\Pages\BlogrSettings::THEME_PRESETS);
    @endphp

    <script>
        (function() {
            var blogrPresets = {!! $presetsJson !!};

            document.addEventListener('change', function(e) {
                var selectEl = e.target;
                if (selectEl.tagName !== 'SELECT') return;
                var name = selectEl.getAttribute('name') || '';
                if (!name.endsWith('theme_preset')) return;

                var preset = selectEl.value;
                if (!preset || !blogrPresets[preset]) return;

                var colors = blogrPresets[preset];
                for (var key in colors) {
                    if (key === 'label') continue;
                    blogrSetColorField('theme_' + key, colors[key]);
                }
            }, true);

            function blogrSetColorField(fieldName, value) {
                var input = document.querySelector(
                    '.fi-fo-color-picker input[name="' + fieldName + '"], ' +
                    '.fi-fo-color-picker input[id$="' + fieldName + '"]'
                );
                if (!input) return;

                var setter = Object.getOwnPropertyDescriptor(
                    window.HTMLInputElement.prototype, 'value'
                ).set;
                setter.call(input, value);
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        })();
    </script>
</x-filament-panels::page>
