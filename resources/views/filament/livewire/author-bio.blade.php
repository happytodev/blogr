<x-filament::section :aside="true" :heading="__('blogr::blogr.profile.bio_heading')" :description="__('blogr::blogr.profile.bio_subheading')">
    <div class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-between gap-4">
            @if(config('blogr.translation.provider', 'none') !== 'none')
                <div>
                    {{ $this->translateBioAction }}
                </div>
            @endif
            <div class="ml-auto">
                <x-filament::button wire:click="submit" wire:loading.attr="disabled" wire:target="submit">
                    {{ __('blogr::blogr.profile.bio_submit') }}
                </x-filament::button>
            </div>
        </div>
    </div>

    @if ($this instanceof \Filament\Actions\Contracts\HasActions)
        <x-filament-actions::modals />
    @endif
</x-filament::section>
