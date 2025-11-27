@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $address = $data['address'] ?? '';
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    $zoom = $data['zoom'] ?? 14;
    $mapId = 'map-iframe-' . uniqid();
    
    // Calculate bbox offset based on zoom level
    // Higher zoom = smaller area = smaller offset
    // Zoom levels: 1 (world) to 19 (building)
    // Formula: offset decreases exponentially with zoom increase
    $baseOffset = 180; // Degrees for zoom level 1 (world view)
    $offset = $baseOffset / pow(2, $zoom - 1);
    
    // Use OpenStreetMap if coordinates provided, otherwise search by address
    if ($latitude && $longitude) {
        $mapUrl = "https://www.openstreetmap.org/export/embed.html?bbox=" . 
                  ($longitude - $offset) . "," . ($latitude - $offset) . "," . 
                  ($longitude + $offset) . "," . ($latitude + $offset) . 
                  "&layer=mapnik&marker={$latitude},{$longitude}";
    } else {
        // Fallback to address search
        $encodedAddress = urlencode($address);
        $mapUrl = "https://www.openstreetmap.org/export/embed.html?bbox=&layer=mapnik&marker=&search={$encodedAddress}";
    }
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading)
            <h2 class="text-3xl sm:text-4xl font-bold mb-8 text-center">
                {{ $heading }}
            </h2>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <div class="space-y-4">
                @if($address)
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ __('Address') }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $address }}</p>
                        </div>
                    </div>
                @endif

                @if($latitude && $longitude)
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">{{ __('Coordinates') }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 font-mono text-sm">
                                {{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}
                            </p>
                        </div>
                    </div>
                @endif

                <a 
                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-semibold"
                >
                    {{ __('Open in Google Maps') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>

            <div class="w-full h-96 rounded-xl overflow-hidden shadow-lg">
                <iframe 
                    id="{{ $mapId }}"
                    class="w-full h-full"
                    frameborder="0"
                    style="border: 0;"
                    loading="lazy"
                    data-src="{{ $mapUrl }}"
                ></iframe>
            </div>
            
            <script>
                (function() {
                    // Delay iframe loading to ensure proper rendering
                    // This prevents the race condition where OpenStreetMap loads before zoom parameters are applied
                    const loadMap = () => {
                        const iframe = document.getElementById('{{ $mapId }}');
                        if (iframe && iframe.dataset.src) {
                            iframe.src = iframe.dataset.src;
                            iframe.removeAttribute('data-src');
                        }
                    };
                    
                    // Use setTimeout with a small delay to ensure DOM is fully rendered
                    // requestAnimationFrame alone is insufficient for iframe zoom parameters
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => {
                            setTimeout(loadMap, 100);
                        });
                    } else {
                        setTimeout(loadMap, 100);
                    }
                })();
            </script>
        </div>
    </div>
</x-blogr::background-wrapper>
