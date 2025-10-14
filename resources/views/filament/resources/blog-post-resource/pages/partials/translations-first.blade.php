{{-- Display the Translations relation manager in header position --}}
<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-header flex flex-col gap-3 p-6">
        <div class="flex items-center gap-3">
            <div class="grid flex-1 gap-y-1">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    📝 Main Content
                </h3>
                <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                    This is where you add the main content of your blog post. Add a translation for each language you want to support. Click "Add a translation" to add your first translation.
                </p>
            </div>
        </div>
    </div>
    
    <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
        @foreach ($relationManagers as $relationManagerClass)
            @php
                $relationManager = \Livewire\Livewire::new($relationManagerClass, [
                    'ownerRecord' => $record,
                    'pageClass' => get_class($this ?? app('livewire')),
                ]);
            @endphp
            
            @livewire($relationManagerClass, [
                'ownerRecord' => $record,
                'pageClass' => get_class($this ?? app('livewire')),
            ])
        @endforeach
    </div>
</div>

