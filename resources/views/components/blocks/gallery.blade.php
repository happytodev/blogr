@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $description = $data['description'] ?? null;
    $images = $data['images'] ?? [];
    $layout = $data['layout'] ?? 'grid';
    $columns = $data['columns'] ?? '3';
    $displayMode = $data['display_mode'] ?? $layout;
    $bwHover = $data['bw_hover'] ?? ($displayMode === 'horizontal');
    $categories = $data['categories'] ?? [];
    $imageCategories = $data['image_categories'] ?? [];

    // Grid layout columns - force string comparison
    $gridCols = match((string)$columns) {
        '2' => 'grid-cols-1 sm:grid-cols-2',
        '3' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        '4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    };

    $imageFilterClass = $bwHover ? 'transition-all duration-500 group-hover:grayscale group-hover:scale-105' : 'transition-transform duration-300 group-hover:scale-105';
    $defaultFilterClass = 'transition-all duration-500 group-hover:grayscale group-hover:scale-105';
@endphp

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

        @if(count($images) > 0)
            <div
                x-data="{
                    lightboxOpen: false,
                    currentIndex: 0,
                    activeFilter: 'all',
                    images: {{ json_encode(array_map(fn($img) => Storage::url($img), $images)) }},
                    @if($displayMode === 'filtered')
                    imageCategories: {{ json_encode($imageCategories) }},
                    get filteredImages() {
                        if (this.activeFilter === 'all') return this.images;
                        return this.images.filter((_, i) => this.imageCategories[String(i)] === this.activeFilter);
                    },
                    @endif
                    openLightbox(index) {
                        this.currentIndex = index;
                        this.lightboxOpen = true;
                        document.body.style.overflow = 'hidden';
                    },
                    closeLightbox() {
                        this.lightboxOpen = false;
                        document.body.style.overflow = 'auto';
                    },
                    nextImage() {
                        this.currentIndex = (this.currentIndex + 1) % this.images.length;
                    },
                    prevImage() {
                        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                    }
                }"
                @keydown.escape.window="closeLightbox()"
                @keydown.arrow-right.window="lightboxOpen && nextImage()"
                @keydown.arrow-left.window="lightboxOpen && prevImage()"
            >
                @if($displayMode === 'filtered' && count($categories) > 0)
                <div class="flex flex-wrap justify-center gap-3 mb-8">
                    <button
                        @click="activeFilter = 'all'"
                        class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-200"
                        :class="activeFilter === 'all' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    >
                        {{ __('All') }}
                    </button>
                    @foreach($categories as $category)
                    <button
                        @click="activeFilter = '{{ $category }}'"
                        class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-200"
                        :class="activeFilter === '{{ $category }}' ? 'bg-[var(--color-primary)] text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                    >
                        {{ $category }}
                    </button>
                    @endforeach
                </div>
                @endif

                @if($displayMode === 'horizontal')
                <div class="overflow-x-auto pb-4 -mx-4 px-4">
                    <div class="flex gap-6 snap-x snap-mandatory">
                        @foreach($images as $index => $image)
                            @php $idx = $loop->index; @endphp
                            <div
                                @click="openLightbox({{ $idx }})"
                                class="snap-start flex-shrink-0 w-[70vw] sm:w-[50vw] lg:w-[40vw] xl:w-[30vw] relative overflow-hidden rounded-lg cursor-pointer group"
                            >
                                <img
                                    src="{{ Storage::url($image) }}"
                                    alt="{{ $heading ?? 'Gallery image ' . ((int) $idx + 1) }}"
                                    class="w-full h-80 object-cover {{ $imageFilterClass }}"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @elseif($displayMode === 'filtered')
                <div class="grid {{ $gridCols }} gap-4">
                    @foreach($images as $index => $image)
                        @php
                            $idx = $loop->index;
                            $cat = $imageCategories[$index] ?? '';
                        @endphp
                        <div
                            x-show="activeFilter === 'all' || activeFilter === '{{ $cat }}'"
                            @click="openLightbox({{ $idx }})"
                            class="relative aspect-square overflow-hidden rounded-lg cursor-pointer group"
                        >
                            <img
                                src="{{ Storage::url($image) }}"
                                alt="{{ $heading ?? 'Gallery image ' . ((int) $idx + 1) }}"
                                class="w-full h-full object-cover {{ $bwHover ? $defaultFilterClass : 'transition-transform duration-300 group-hover:scale-105' }}"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>

                @elseif($layout === 'grid')
                    <div class="grid {{ $gridCols }} gap-4">
                        @foreach($images as $index => $image)
                            @php $idx = $loop->index; @endphp
                            <div
                                @click="openLightbox({{ $idx }})"
                                class="relative aspect-square overflow-hidden rounded-lg cursor-pointer group"
                            >
                                <img
                                    src="{{ Storage::url($image) }}"
                                    alt="{{ $heading ?? 'Gallery image ' . ((int) $idx + 1) }}"
                                    class="w-full h-full object-cover {{ $bwHover ? $defaultFilterClass : 'transition-transform duration-300 group-hover:scale-105' }}"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center pointer-events-none group-hover:pointer-events-auto">
                                    <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @elseif($layout === 'masonry')
                    <div class="grid {{ $gridCols }} gap-4">
                        @foreach($images as $index => $image)
                            @php
                                $idx = $loop->index;
                                $aspectClasses = ['aspect-square', 'aspect-[3/4]', 'aspect-[4/3]', 'aspect-[3/4]', 'aspect-square', 'aspect-[4/3]'];
                                $aspectClass = $aspectClasses[$idx % count($aspectClasses)];
                            @endphp
                            <div
                                @click="openLightbox({{ $idx }})"
                                class="relative {{ $aspectClass }} overflow-hidden rounded-lg cursor-pointer group"
                            >
                                <img
                                    src="{{ Storage::url($image) }}"
                                    alt="{{ $heading ?? 'Gallery image ' . ((int) $idx + 1) }}"
                                    class="w-full h-full object-cover {{ $bwHover ? $defaultFilterClass : 'transition-transform duration-300 group-hover:scale-105' }}"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center pointer-events-none group-hover:pointer-events-auto">
                                    <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @elseif($layout === 'bento')
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 auto-rows-[200px]">
                        @foreach($images as $index => $image)
                            @php
                                $idx = $loop->index;
                                $pattern = [
                                    'col-span-2 row-span-2',
                                    'col-span-1 row-span-1',
                                    'col-span-1 row-span-1',
                                    'col-span-1 row-span-2',
                                    'col-span-1 row-span-1',
                                    'col-span-2 row-span-1',
                                ];
                                $spanClass = $pattern[$idx % count($pattern)] ?? 'col-span-1 row-span-1';
                            @endphp

                            <div
                                @click="openLightbox({{ $idx }})"
                                class="relative {{ $spanClass }} overflow-hidden rounded-lg cursor-pointer group"
                            >
                                <img
                                    src="{{ Storage::url($image) }}"
                                    alt="{{ $heading ?? 'Gallery image ' . ((int) $idx + 1) }}"
                                    class="w-full h-full object-cover {{ $bwHover ? $defaultFilterClass : 'transition-transform duration-300 group-hover:scale-105' }}"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center pointer-events-none group-hover:pointer-events-auto">
                                    <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div
                    x-show="lightboxOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90"
                    style="display: none;"
                    @click="closeLightbox()"
                >
                    <button
                        @click.stop="closeLightbox()"
                        class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10"
                        aria-label="Close lightbox"
                    >
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <button
                        @click.stop="prevImage()"
                        class="absolute left-4 text-white hover:text-gray-300 transition-colors z-10"
                        x-show="images.length > 1"
                        aria-label="Previous image"
                    >
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <div class="max-w-7xl max-h-[90vh] p-4" @click.stop>
                        <img
                            :src="images[currentIndex]"
                            :alt="'{{ $heading ?? 'Gallery image' }} ' + (currentIndex + 1)"
                            class="max-w-full max-h-full object-contain"
                        >
                    </div>

                    <button
                        @click.stop="nextImage()"
                        class="absolute right-4 text-white hover:text-gray-300 transition-colors z-10"
                        x-show="images.length > 1"
                        aria-label="Next image"
                    >
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm">
                        <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
