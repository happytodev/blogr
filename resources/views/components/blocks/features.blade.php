@props(['data'])

@php
$title = $data['title'] ?? '';
$subtitle = $data['subtitle'] ?? '';
$items = $data['items'] ?? [];
$columns = $data['columns'] ?? '3';
$gridClass = match($columns) {
    '2' => 'grid-cols-1 md:grid-cols-2',
    '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
};
@endphp

<x-blogr::background-wrapper :data="$data" class="py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ $title }}
                </h2>
                @if($subtitle)
                    <p class="subtitle text-lg max-w-3xl mx-auto">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        @endif
        
        <div class="grid {{ $gridClass }} gap-8">
            @foreach($items as $item)
                <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    @if(!empty($item['icon']))
                        <div class="mb-4">
                            <x-dynamic-component :component="$item['icon']" class="w-12 h-12 text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]" />
                        </div>
                    @endif
                    <h3 class="text-xl font-bold mb-2">
                        {{ $item['title'] }}
                    </h3>
                    <p>
                        {{ $item['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</x-blogr::background-wrapper>
