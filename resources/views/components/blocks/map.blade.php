@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $subtitle = $data['subtitle'] ?? null;
    $tagline = $data['tagline'] ?? 'Made with love in the world capital of perfume';
    $taglinePosition = $data['tagline_position'] ?? 'bottom';
    // Support both new (center_lat/center_lng) and old (latitude/longitude) field names
    $centerLat = $data['center_lat'] ?? $data['latitude'] ?? 43.6589;
    $centerLng = $data['center_lng'] ?? $data['longitude'] ?? 6.9252;
    $zoom = max(1, min(19, (int)($data['zoom'] ?? 15)));
    $height = max(300, min(800, (int)($data['height'] ?? 480)));
    $markers = $data['markers'] ?? [];
    $mapId = 'leaflet-map-' . uniqid();
    $appName = config('app.name', 'Blogr');
@endphp

<link rel="preconnect" href="https://unpkg.com">

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading || $subtitle)
            <div class="text-center mb-10">
                @if($heading)
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        {{ $heading }}
                    </h2>
                @endif
                @if($subtitle)
                    <p class="mt-3 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        @endif

        <div class="relative rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700" style="min-height: {{ $height }}px;">

            {{-- Server-rendered static fallback — always visible, Leaflet JS enhances on top --}}
            <div id="{{ $mapId }}-fallback" class="leaflet-static-fallback absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 text-center p-6">
                <span class="text-4xl mb-3">📍</span>
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $tagline }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $centerLat }}, {{ $centerLng }} · {{ $zoom }}z</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">{{ $appName }} · <a href="https://leafletjs.com" target="_blank" rel="noopener noreferrer" class="underline">Leaflet</a></p>
            </div>

            {{-- Leaflet interactive container — overlays the fallback once JS loads --}}
            <div id="{{ $mapId }}" class="w-full h-full absolute inset-0 z-20" style="min-height: {{ $height }}px; display: none;"></div>

            {{-- Error fallback shown if Leaflet CDN fails --}}
            <div id="{{ $mapId }}-error" class="leaflet-error absolute inset-0 z-30 hidden flex-col items-center justify-center bg-red-50 dark:bg-red-900/30 text-center p-6">
                <span class="text-3xl mb-2">⚠️</span>
                <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ __('Map could not be loaded. Please check your internet connection.') }}</p>
            </div>

            @if($tagline && $taglinePosition === 'bottom')
                <div class="absolute bottom-0 left-0 right-0 z-[1000] bg-gradient-to-t from-black/70 via-black/40 to-transparent pointer-events-none">
                    <div class="px-6 py-5 pointer-events-auto">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">💜</span>
                            <p class="text-white text-base sm:text-lg font-medium drop-shadow-lg">
                                {{ $tagline }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                <span>© <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="underline hover:text-gray-600 dark:hover:text-gray-300">OpenStreetMap</a></span>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <span><a href="https://leafletjs.com" target="_blank" rel="noopener noreferrer" class="underline hover:text-gray-600 dark:hover:text-gray-300">Leaflet</a></span>
            </div>
            @if($tagline && $taglinePosition === 'hidden')
                <p class="text-xs text-gray-400 dark:text-gray-500 italic">{{ $tagline }}</p>
            @endif
        </div>
    </div>

    <noscript>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-2">
            <p class="text-xs text-amber-600 dark:text-amber-400 text-center">
                {{ __('Enable JavaScript for an interactive map.') }}
            </p>
        </div>
    </noscript>

    {{-- Load Leaflet via static <script> tag — no dynamic injection, reliable --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""
        onerror="document.getElementById('{{ $mapId }}-error')?.classList.remove('hidden'); document.getElementById('{{ $mapId }}-error')?.classList.add('flex');">
    </script>

    <script>
        (function() {
            var mapId = '{{ $mapId }}';
            var centerLat = {{ $centerLat }};
            var centerLng = {{ $centerLng }};
            var zoom = {{ $zoom }};
            var markers = @json($markers);
            var fallbackEl = document.getElementById(mapId + '-fallback');
            var errorEl = document.getElementById(mapId + '-error');
            var container = document.getElementById(mapId);

            var initMap = function() {
                if (typeof L === 'undefined' || !container) return;

                var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var lightTile = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                var darkTile = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
                var lightAttr = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
                var darkAttr = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>';

                var map = L.map(container, {
                    center: [centerLat, centerLng],
                    zoom: zoom,
                    zoomControl: true,
                    scrollWheelZoom: true,
                });

                L.tileLayer(isDark ? darkTile : lightTile, {
                    attribution: isDark ? darkAttr : lightAttr,
                    maxZoom: 19,
                }).addTo(map);

                if (fallbackEl) {
                    fallbackEl.style.display = 'none';
                }
                container.style.display = 'block';

                setTimeout(function() {
                    map.invalidateSize();
                }, 100);

                var markerIcon = L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="width:36px;height:36px;background:#4f46e5;border:4px solid white;border-radius:50%;box-shadow:0 3px 12px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;font-size:18px;line-height:1;">📍</div>',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    popupAnchor: [0, -20],
                });

                if (markers && markers.length > 0) {
                    markers.forEach(function(m) {
                        var lat = parseFloat(m.lat) || centerLat;
                        var lng = parseFloat(m.lng) || centerLng;
                        var popup = m.popup_text || '';
                        var marker = L.marker([lat, lng], { icon: markerIcon }).addTo(map);
                        if (popup) {
                            marker.bindPopup('<div class="text-sm font-medium px-1 py-0.5">' + popup + '</div>');
                        }
                    });
                } else {
                    L.marker([centerLat, centerLng], { icon: markerIcon }).addTo(map)
                        .bindPopup('<div class="text-sm font-medium px-1 py-0.5">📍 Grasse — World Capital of Perfume</div>');
                }

                if (window.ResizeObserver) {
                    var ro = new ResizeObserver(function() {
                        map.invalidateSize();
                    });
                    ro.observe(container);
                }

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
                    location.reload();
                });
            };

            initMap();
        })();
    </script>

    <style>
        #{{ $mapId }} .leaflet-popup-content-wrapper { border-radius: 10px; padding: 2px; }
        #{{ $mapId }} .leaflet-popup-tip { background: white; }
        .custom-marker { background: none !important; border: none !important; }
    </style>
</x-blogr::background-wrapper>
