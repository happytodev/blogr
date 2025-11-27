@props(['data'])

@php
$title = $data['title'] ?? '';
$items = $data['items'] ?? [];
@endphp

<x-blogr::background-wrapper :data="$data" class="py-16 sm:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <h2 class="text-3xl sm:text-4xl font-bold mb-12 text-center">
                {{ $title }}
            </h2>
        @endif
        
        <div class="space-y-4" x-data="{ activeItem: null }">
            @foreach($items as $index => $item)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <button @click="activeItem = activeItem === {{ $index }} ? null : {{ $index }}"
                            class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <span class="font-semibold">
                            {{ $item['question'] }}
                        </span>
                        <svg x-show="activeItem !== {{ $index }}" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <svg x-show="activeItem === {{ $index }}" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                    <div x-show="activeItem === {{ $index }}" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <p>
                            {{ $item['answer'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-blogr::background-wrapper>
