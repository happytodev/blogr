@props(['data'])

@php
$title = $data['title'] ?? '';
$subtitle = $data['subtitle'] ?? '';
$image = $data['image'] ?? '';
$ctaText = $data['cta_text'] ?? '';
$ctaUrl = $data['cta_url'] ?? '';
$alignment = $data['alignment'] ?? 'center';

$alignmentClass = match($alignment) {
    'left' => 'text-left items-start',
    'right' => 'text-right items-end',
    default => 'text-center items-center',
};
@endphp

<x-blogr::background-wrapper :data="$data" class="text-white">
    @if($image)
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('storage/' . $image) }}" alt="{{ $title }}" class="w-full h-full object-cover opacity-20">
        </div>
    @endif
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="flex flex-col {{ $alignmentClass }} space-y-8">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold leading-tight max-w-4xl">
                {{ $title }}
            </h1>
            
            @if($subtitle)
                <p class="text-xl sm:text-2xl text-white/90 max-w-3xl">
                    {{ $subtitle }}
                </p>
            @endif
            
            @if($ctaText && $ctaUrl)
                <div class="pt-4">
                    <a href="{{ $ctaUrl }}" 
                       class="inline-flex items-center px-8 py-4 bg-white text-[var(--color-primary)] dark:bg-gray-900 dark:text-[var(--color-primary-dark)] rounded-lg font-semibold text-lg hover:scale-105 transition-transform duration-200 shadow-xl">
                        {{ $ctaText }}
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-blogr::background-wrapper>    <!-- Decorative wave -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="currentColor" class="text-gray-50 dark:text-gray-900"/>
        </svg>
    </div>
</section>
