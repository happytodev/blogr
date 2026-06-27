@php
$slides = array_values(array_filter($data['slides'] ?? [], fn($s) => $s !== null));
$height = $data['height'] ?? 'md';
$autoplaySpeed = $data['autoplay_speed'] ?? 5000;
$showArrows = $data['show_arrows'] ?? true;
$showDots = $data['show_dots'] ?? true;

$normalizeImage = function ($image) {
    if (is_array($image)) {
        $first = reset($image);
        return ($first ?? null) ?: null;
    }
    return ($image ?? null) ?: null;
};

$heightClass = match($height) {
    'sm' => 'h-[400px]',
    'md' => 'h-[500px]',
    'lg' => 'h-[600px]',
    'fullscreen' => 'h-screen',
    default => 'h-[500px]',
};
@endphp

@if(count($slides) > 0)
<x-blogr::background-wrapper :data="$data">
    <div
        x-data="{
            currentSlide: 0,
            slides: {{ json_encode(array_map(fn($s) => [
                'image' => ($img = $normalizeImage($s['image'])) ? Storage::url($img) : '',
                'title' => $s['title'] ?? '',
                'subtitle' => $s['subtitle'] ?? '',
                'cta_text' => $s['cta_text'] ?? '',
                'cta_url' => $s['cta_url'] ?? '',
            ], $slides)) }},
            autoplaySpeed: {{ $autoplaySpeed }},
            autoplayInterval: null,

            init() {
                if (this.slides.length > 1 && this.autoplaySpeed > 0) {
                    this.startAutoplay();
                }
            },

            startAutoplay() {
                this.stopAutoplay();
                this.autoplayInterval = setInterval(() => {
                    this.next();
                }, this.autoplaySpeed);
            },

            stopAutoplay() {
                if (this.autoplayInterval) {
                    clearInterval(this.autoplayInterval);
                    this.autoplayInterval = null;
                }
            },

            next() {
                this.currentSlide = (this.currentSlide + 1) % this.slides.length;
            },

            prev() {
                this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
            },

            goTo(index) {
                this.currentSlide = index;
                if (this.slides.length > 1 && this.autoplaySpeed > 0) {
                    this.startAutoplay();
                }
            }
        }"
        x-on:mouseenter="stopAutoplay()"
        x-on:mouseleave="startAutoplay()"
        class="relative {{ $heightClass }} overflow-hidden"
    >
        @foreach($slides as $index => $slide)
        <div
            x-show="currentSlide === {{ $index }}"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute inset-0"
            style="{{ $index === 0 ? '' : 'display: none;' }}"
        >
            <div class="relative w-full h-full">
                @if($slideImg = $normalizeImage($slide['image']))
                <img
                    src="{{ Storage::url($slideImg) }}"
                    alt="{{ $slide['title'] ?? 'Slide ' . ((int) $index + 1) }}"
                    class="w-full h-full object-cover"
                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                >
                <div class="absolute inset-0 bg-black/40"></div>
                @endif

                @if(!empty($slide['title']) || !empty($slide['subtitle']) || !empty($slide['cta_text']))
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-white px-4 max-w-4xl">
                        @if(!empty($slide['title']))
                        <h2 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                            {{ $slide['title'] }}
                        </h2>
                        @endif

                        @if(!empty($slide['subtitle']))
                        <p class="text-xl sm:text-2xl mb-6 drop-shadow-md">
                            {{ $slide['subtitle'] }}
                        </p>
                        @endif

                        @if(!empty($slide['cta_text']) && !empty($slide['cta_url']))
                        <a
                            href="{{ $slide['cta_url'] }}"
                            class="inline-flex items-center px-8 py-4 bg-white text-gray-900 rounded-lg font-semibold text-lg hover:scale-105 transition-transform duration-200 shadow-xl"
                        >
                            {{ $slide['cta_text'] }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        @if($showArrows && count($slides) > 1)
        <button
            @click="prev()"
            class="carousel-prev absolute left-4 top-1/2 -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors bg-black/20 hover:bg-black/40 rounded-full p-2"
            aria-label="Previous slide"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button
            @click="next()"
            class="carousel-next absolute right-4 top-1/2 -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors bg-black/20 hover:bg-black/40 rounded-full p-2"
            aria-label="Next slide"
        >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        @endif

        @if($showDots && count($slides) > 1)
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex space-x-3">
            @foreach($slides as $index => $slide)
            <button
                @click="goTo({{ $index }})"
                class="w-3 h-3 rounded-full transition-all duration-300"
                :class="currentSlide === {{ $index }} ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'"
                    aria-label="Go to slide {{ (int) $index + 1 }}"
            ></button>
            @endforeach
        </div>
        @endif
    </div>
</x-blogr::background-wrapper>
@endif
