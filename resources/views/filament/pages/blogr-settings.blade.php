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
</x-filament-panels::page>
