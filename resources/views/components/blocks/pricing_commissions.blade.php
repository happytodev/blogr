@php
$heading = $data['heading'] ?? null;
$description = $data['description'] ?? null;
$items = $data['items'] ?? [];
$layout = $data['layout'] ?? 'carousel';

$normalizeImage = function ($image) {
    if (is_array($image)) {
        return $image[0] ?? '';
    }
    return $image ?? '';
};

$statusLabels = [
    'open' => __('Open'),
    'closed' => __('Closed'),
    'on_request' => __('On Request'),
];

$statusColors = [
    'open' => 'bg-green-500',
    'closed' => 'bg-red-500',
    'on_request' => 'bg-blue-500',
];
@endphp

@if(count($items) > 0)
<x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading || $description)
            <div class="text-center mb-12">
                @if($heading)
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                        {{ $heading }}
                    </h2>
                @endif

                @if($description)
                    <p class="subtitle text-xl">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        @if($layout === 'carousel')
        <div
            x-data="{
                scrollContainer: null,
                init() {
                    this.scrollContainer = $el.querySelector('.commission-scroll');
                },
                scroll(direction) {
                    if (this.scrollContainer) {
                        const amount = this.scrollContainer.clientWidth * 0.8;
                        this.scrollContainer.scrollBy({ left: direction * amount, behavior: 'smooth' });
                    }
                }
            }"
            class="relative"
        >
            <button
                @click="scroll(-1)"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-10 -ml-4 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-[var(--color-primary)] transition-colors"
                aria-label="Scroll left"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <div class="overflow-x-auto pb-4 -mx-4 px-4 commission-scroll snap-x snap-mandatory scroll-smooth">
                <div class="flex gap-6">
                    @foreach($items as $item)
                    <div class="snap-start flex-shrink-0 w-[300px] group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 hover:shadow-xl">
                        <div class="relative h-64 overflow-hidden">
                            @if(!empty($item['image']))
                            <img
                                src="{{ Storage::url($normalizeImage($item['image'])) }}"
                                alt="{{ $item['title'] ?? '' }}"
                                class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105 group-hover:grayscale"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            @endif

                            <div class="absolute inset-0 flex flex-col justify-end p-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                @if(!empty($item['description']))
                                <p class="text-white text-sm mb-2 drop-shadow-lg">
                                    {{ $item['description'] }}
                                </p>
                                @endif

                                @if(!empty($item['price']))
                                <p class="text-white text-2xl font-bold drop-shadow-lg">
                                    {{ $item['price'] }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center justify-between mb-2">
                                @if(!empty($item['title']))
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $item['title'] }}
                                </h3>
                                @endif

                                @if(!empty($item['status']) && isset($statusColors[$item['status']]))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$item['status']] }} text-white">
                                    {{ $statusLabels[$item['status']] ?? $item['status'] }}
                                </span>
                                @endif
                            </div>

                            @if(!empty($item['price']))
                            <p class="text-lg font-bold text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">
                                {{ $item['price'] }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button
                @click="scroll(1)"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-10 -mr-4 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-[var(--color-primary)] transition-colors"
                aria-label="Scroll right"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $item)
            <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 hover:shadow-xl">
                <div class="relative h-64 overflow-hidden">
                    @if(!empty($item['image']))
                    <img
                        src="{{ Storage::url($normalizeImage($item['image'])) }}"
                        alt="{{ $item['title'] ?? '' }}"
                        class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105 group-hover:grayscale"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    @endif

                    <div class="absolute inset-0 flex flex-col justify-end p-5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        @if(!empty($item['description']))
                        <p class="text-white text-sm mb-2 drop-shadow-lg">
                            {{ $item['description'] }}
                        </p>
                        @endif

                        @if(!empty($item['price']))
                        <p class="text-white text-2xl font-bold drop-shadow-lg">
                            {{ $item['price'] }}
                        </p>
                        @endif
                    </div>
                </div>

                <div class="p-5">
                    <div class="flex items-center justify-between mb-2">
                        @if(!empty($item['title']))
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $item['title'] }}
                        </h3>
                        @endif

                        @if(!empty($item['status']) && isset($statusColors[$item['status']]))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$item['status']] }} text-white">
                            {{ $statusLabels[$item['status']] ?? $item['status'] }}
                        </span>
                        @endif
                    </div>

                    @if(!empty($item['price']))
                    <p class="text-lg font-bold text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]">
                        {{ $item['price'] }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-blogr::background-wrapper>
@endif
