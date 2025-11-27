{{-- No @props needed when using @include - $data is passed directly --}}

@php
use Happytodev\Blogr\Helpers\LinkResolver;

$title = $data['title'] ?? '';
$subtitle = $data['subtitle'] ?? '';
$image = $data['image'] ?? '';
$ctaText = $data['cta_text'] ?? '';
$ctaUrl = LinkResolver::resolve($data, 'cta_link_type', 'cta_url', 'cta_category_id', 'cta_cms_page_id');

// Show button if both text and valid URL are present
// With improved LinkResolver, URLs should always be generated for all link types
$showButton = !empty($ctaText) && !empty($ctaUrl);

$alignment = $data['alignment'] ?? 'center';
$imagePosition = $data['image_position'] ?? 'top';
$imageMaxWidth = $data['image_max_width'] ?? 'max-w-2xl';

// Map Tailwind classes to actual pixel values for inline styles
$maxWidthMap = [
    'max-w-sm' => '384px',
    'max-w-md' => '448px',
    'max-w-lg' => '512px',
    'max-w-xl' => '576px',
    'max-w-2xl' => '672px',
    'max-w-3xl' => '768px',
    'max-w-4xl' => '896px',
    'max-w-5xl' => '1024px',
    'max-w-full' => '100%',
];

$imageMaxWidthPx = $maxWidthMap[$imageMaxWidth] ?? '672px';

// Ensure image path is correct - Filament may return full path already
$imagePath = $image;
if ($imagePath && !str_starts_with($imagePath, 'storage/')) {
    $imagePath = 'storage/' . $imagePath;
}

$alignmentClass = match($alignment) {
    'left' => 'text-left',
    'right' => 'text-right',
    default => 'text-center',
};

$layoutClass = match($imagePosition) {
    'left' => 'flex-row-reverse',
    'right' => 'flex-row',
    default => 'flex-col', // top
};
@endphp

<x-blogr::background-wrapper :data="$data" class="text-white">
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="flex {{ $layoutClass }} gap-12">
            {{-- Image Section --}}
            @if($imagePath)
                <div class="{{ $imagePosition === 'top' ? 'w-full flex justify-center px-4' : 'w-1/3 flex-shrink-0' }}">
                    <img src="{{ asset($imagePath) }}" 
                         alt="{{ $title }}" 
                         class="rounded-lg shadow-2xl {{ $imagePosition === 'top' ? 'h-auto object-cover' : 'w-full h-auto object-cover' }}"
                         style="{{ $imagePosition === 'top' ? 'max-width: ' . $imageMaxWidthPx . '; width: 100%;' : '' }}"
                         loading="lazy">
                </div>
            @endif

            {{-- Text Content --}}
            <div class="{{ $imagePosition === 'top' ? 'w-full' : 'flex-1' }} flex flex-col {{ $alignmentClass }} justify-center space-y-8">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold leading-tight drop-shadow-lg">
                    {{ $title }}
                </h1>
                
                @if($subtitle)
                    <p class="subtitle text-xl sm:text-2xl drop-shadow-md">
                        {{ $subtitle }}
                    </p>
                @endif
                
                @if($showButton)
                    <div class="pt-4 {{ $alignment === 'center' ? 'flex justify-center' : ($alignment === 'left' ? '' : 'flex justify-end') }}">
                        <a href="{{ $ctaUrl }}" 
                           class="inline-flex items-center px-8 py-4 bg-white text-blue-600 dark:bg-gray-900 dark:text-blue-400 rounded-lg font-semibold text-lg hover:scale-105 transition-transform duration-200 shadow-xl">
                            {{ $ctaText }}
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-blogr::background-wrapper>
