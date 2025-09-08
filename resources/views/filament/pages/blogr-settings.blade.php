<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6">
            {{ $this->form }}
        </div>

        <div class="flex justify-end !pt-12 !mt-8 border-t">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
