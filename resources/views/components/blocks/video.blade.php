@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $url = $data['url'] ?? '';
    $aspectRatio = $data['aspect_ratio'] ?? '16/9';
    
    // Detect video platform and extract ID
    $embedUrl = '';
    if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches);
        $videoId = $matches[1] ?? '';
        $embedUrl = "https://www.youtube.com/embed/{$videoId}";
    } elseif (str_contains($url, 'vimeo.com')) {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
        $videoId = $matches[1] ?? '';
        $embedUrl = "https://player.vimeo.com/video/{$videoId}";
    }
    
    // Aspect ratio classes
    $aspectClass = match($aspectRatio) {
        '4/3' => 'aspect-[4/3]',
        '21/9' => 'aspect-[21/9]',
        default => 'aspect-video', // 16/9
    };
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading)
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">
                {{ $heading }}
            </h2>
        @endif

        @if($embedUrl)
            <div class="{{ $aspectClass }} w-full rounded-xl overflow-hidden shadow-2xl">
                <iframe 
                    src="{{ $embedUrl }}" 
                    class="w-full h-full"
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                ></iframe>
            </div>
        @else
            <div class="{{ $aspectClass }} w-full rounded-xl overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                <div class="text-center px-4">
                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Video URL not configured') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
