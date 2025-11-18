@props(['data'])

@php
$waveEnabled = $data['wave_enabled'] ?? true;
$waveStyle = $data['wave_style'] ?? 'wave';
$waveColorLight = $data['wave_color_light'] ?? '#f8f8f8';
$waveColorDark = $data['wave_color_dark'] ?? '#111827';
$waveAmplitude = $data['wave_amplitude'] ?? 'medium';
$waveOpacity = (int)($data['wave_opacity'] ?? 100) / 100;
$waveFillStyle = $data['wave_fill_style'] ?? 'fill'; // 'fill' or 'stroke'

// Generate gradient IDs
$lightGradientId = 'wave-light-' . md5(json_encode($data));
$darkGradientId = 'wave-dark-' . md5(json_encode($data));

// Wave class for animations
$waveClass = $waveStyle === 'wave' ? 'animate-wave' : '';

// Generate wave SVG path based on style and amplitude
$wavePaths = [
    'wave' => [
        'low' => 'M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z',
        'medium' => 'M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z',
        'high' => 'M0 120L60 90C120 60 240 30 360 20C480 10 600 10 720 30C840 50 960 80 1080 85C1200 90 1320 75 1380 60L1440 40V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z',
    ],
    'wave-2' => [
        'low' => 'M0 120L30 115C60 110 120 100 180 100C240 100 300 100 360 100C420 100 480 100 540 105C600 110 660 115 720 110C780 105 840 85 900 80C960 75 1020 75 1080 80C1140 85 1200 95 1260 100C1320 105 1380 105 1440 105V120H1380C1320 120 1260 120 1200 120C1140 120 1080 120 1020 120C960 120 900 120 840 120C780 120 720 120 660 120C600 120 540 120 480 120C420 120 360 120 300 120C240 120 180 120 120 120C60 120 0 120 0 120Z',
        'medium' => 'M0 120L30 105C60 90 120 60 180 45C240 30 300 30 360 35C420 40 480 60 540 70C600 80 660 80 720 75C780 70 840 50 900 40C960 30 1020 30 1080 45C1140 60 1200 90 1260 105C1320 120 1380 120 1440 120V120H0Z',
        'high' => 'M0 120L30 90C60 60 120 20 180 10C240 0 300 20 360 40C420 60 480 80 540 85C600 90 660 80 720 60C780 40 840 20 900 10C960 0 1020 20 1080 50C1140 80 1200 110 1260 120C1320 130 1380 120 1440 110V120H0Z',
    ],
    'wave-3' => [
        'low' => 'M0 120L24 116C48 112 96 104 144 104C192 104 240 112 288 112C336 112 384 104 432 104C480 104 528 112 576 114C624 116 672 112 720 108C768 104 816 96 864 96C912 96 960 104 1008 108C1056 112 1104 112 1152 110C1200 108 1248 102 1296 102C1344 102 1392 108 1440 112V120H1440C1392 120 1344 120 1296 120C1248 120 1200 120 1152 120C1104 120 1056 120 1008 120C960 120 912 120 864 120C816 120 768 120 720 120C672 120 624 120 576 120C528 120 480 120 432 120C384 120 336 120 288 120C240 120 192 120 144 120C96 120 48 120 24 120H0Z',
        'medium' => 'M0 120L24 108C48 96 96 72 144 60C192 48 240 48 288 54C336 60 384 78 432 84C480 90 528 84 576 78C624 72 672 60 720 54C768 48 816 48 864 60C912 72 960 96 1008 108C1056 120 1104 120 1152 114C1200 108 1248 84 1296 72C1344 60 1392 60 1440 66V120H1440C1392 120 1344 120 1296 120C1248 120 1200 120 1152 120C1104 120 1056 120 1008 120C960 120 912 120 864 120C816 120 768 120 720 120C672 120 624 120 576 120C528 120 480 120 432 120C384 120 336 120 288 120C240 120 192 120 144 120C96 120 48 120 24 120H0Z',
        'high' => 'M0 120L24 80C48 40 96 0 144 0C192 0 240 40 288 60C336 80 384 80 432 70C480 60 528 40 576 30C624 20 672 20 720 40C768 60 816 100 864 110C912 120 960 100 1008 80C1056 60 1104 40 1152 30C1200 20 1248 20 1296 40C1344 60 1392 100 1440 110V120H0Z',
    ],
    'curve' => [
        'low' => 'M0 120Q360 110 720 110T1440 120V120H0Z',
        'medium' => 'M0 120Q360 80 720 80T1440 120V120H0Z',
        'high' => 'M0 120Q360 40 720 40T1440 120V120H0Z',
    ],
];

// Helper function to adjust brightness (convert hex to RGB, adjust, convert back)
function adjustBrightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    [$r, $g, $b] = sscanf($hex, "%02x%02x%02x");
    
    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));
    
    return '#' . dechex($r) . dechex($g) . dechex($b);
}

$wavePath = $wavePaths[$waveStyle][$waveAmplitude] ?? $wavePaths['wave']['medium'];
@endphp

@if($waveEnabled)
<!-- Decorative wave (configurable) -->
<div class="absolute bottom-0 left-0 right-0">
    <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto" preserveAspectRatio="none">
        <defs>
            <!-- Light mode gradient -->
            <linearGradient id="{{ $lightGradientId }}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:{{ $waveColorLight }};stop-opacity:{{ $waveOpacity }}" />
                <stop offset="100%" style="stop-color:{{ $waveColorLight }};stop-opacity:{{ $waveOpacity * 0.5 }}" />
            </linearGradient>
            <!-- Dark mode gradient -->
            <linearGradient id="{{ $darkGradientId }}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:{{ $waveColorDark }};stop-opacity:{{ $waveOpacity }}" />
                <stop offset="100%" style="stop-color:{{ $waveColorDark }};stop-opacity:{{ $waveOpacity * 0.5 }}" />
            </linearGradient>
        </defs>

        @if(in_array($waveStyle, ['wave-2', 'wave-3']))
            <!-- Multiple waves with varying opacities and different paths -->
            @php
                $waveCount = $waveStyle === 'wave-2' ? 2 : 3;
            @endphp
            
            @for($i = 0; $i < $waveCount; $i++)
                @php
                    // Generate color variations for each wave
                    // Wave 0: base color
                    // Wave 1: lighter by 10%
                    // Wave 2: darker by 10%
                    $colorVariations = [
                        0 => $waveColorLight, // Original
                        1 => adjustBrightness($waveColorLight, 10), // 10% lighter
                        2 => adjustBrightness($waveColorLight, -10), // 10% darker
                    ];
                    $waveColor = $colorVariations[$i] ?? $waveColorLight;
                    
                    // Use amplitude variations to create different wave frequencies
                    $amplitudeVariations = [
                        'low' => [60, 80, 100],
                        'medium' => [50, 75, 95],
                        'high' => [30, 60, 80],
                    ];
                    $ampValues = $amplitudeVariations[$waveAmplitude] ?? $amplitudeVariations['medium'];
                    
                    // For stroke mode, use open paths (no bottom closure)
                    // For fill mode, use closed paths (with bottom closure)
                    if ($waveFillStyle === 'stroke') {
                        // Open paths for stroke - no closing lines
                        $customWavePaths = [
                            // Wave 1 - Long wavelength (slow)
                            'M0 ' . $ampValues[0] . ' Q360 ' . ($ampValues[0] - 30) . ' 720 ' . $ampValues[0] . ' T1440 ' . $ampValues[0],
                            // Wave 2 - Medium wavelength (offset phase)
                            'M0 ' . $ampValues[1] . ' Q240 ' . ($ampValues[1] - 20) . ' 480 ' . $ampValues[1] . ' Q720 ' . ($ampValues[1] + 10) . ' 960 ' . $ampValues[1] . ' Q1200 ' . ($ampValues[1] - 15) . ' 1440 ' . $ampValues[1],
                            // Wave 3 - Short wavelength (fast)
                            'M0 ' . $ampValues[2] . ' Q120 ' . ($ampValues[2] - 15) . ' 240 ' . $ampValues[2] . ' Q360 ' . ($ampValues[2] + 8) . ' 480 ' . $ampValues[2] . ' Q600 ' . ($ampValues[2] - 12) . ' 720 ' . $ampValues[2] . ' Q840 ' . ($ampValues[2] + 10) . ' 960 ' . $ampValues[2] . ' Q1080 ' . ($ampValues[2] - 8) . ' 1200 ' . $ampValues[2] . ' Q1320 ' . ($ampValues[2] + 6) . ' 1440 ' . $ampValues[2],
                        ];
                    } else {
                        // Closed paths for fill
                        $customWavePaths = [
                            // Wave 1 - Long wavelength (slow)
                            'M0 ' . $ampValues[0] . ' Q360 ' . ($ampValues[0] - 30) . ' 720 ' . $ampValues[0] . ' T1440 ' . $ampValues[0] . ' V120H0Z',
                            // Wave 2 - Medium wavelength (offset phase)
                            'M0 ' . $ampValues[1] . ' Q240 ' . ($ampValues[1] - 20) . ' 480 ' . $ampValues[1] . ' Q720 ' . ($ampValues[1] + 10) . ' 960 ' . $ampValues[1] . ' Q1200 ' . ($ampValues[1] - 15) . ' 1440 ' . $ampValues[1] . ' V120H0Z',
                            // Wave 3 - Short wavelength (fast)
                            'M0 ' . $ampValues[2] . ' Q120 ' . ($ampValues[2] - 15) . ' 240 ' . $ampValues[2] . ' Q360 ' . ($ampValues[2] + 8) . ' 480 ' . $ampValues[2] . ' Q600 ' . ($ampValues[2] - 12) . ' 720 ' . $ampValues[2] . ' Q840 ' . ($ampValues[2] + 10) . ' 960 ' . $ampValues[2] . ' Q1080 ' . ($ampValues[2] - 8) . ' 1200 ' . $ampValues[2] . ' Q1320 ' . ($ampValues[2] + 6) . ' 1440 ' . $ampValues[2] . ' V120H0Z',
                        ];
                    }
                    
                    $currentOpacity = $waveOpacity * (1 - ($i * 0.2));
                    $currentOffset = $i * 12;
                @endphp
                <g style="opacity: {{ $currentOpacity }}; transform: translateY({{ $currentOffset }}px);">
                    <path d="{{ $customWavePaths[$i] ?? $customWavePaths[0] }}" 
                          class="{{ $waveClass }}" 
                          style="stroke-width: {{ $waveFillStyle === 'stroke' ? '2' : '0' }}px;@if($waveFillStyle === 'fill') fill: {{ $waveColor }}; @else stroke: {{ $waveColor }}; @endif" />
                </g>
            @endfor
        @else
            <!-- Single wave -->
            @php
                if ($waveFillStyle === 'stroke') {
                    // Open path for stroke
                    $singleWavePath = str_replace(' V120H0Z', '', $wavePath);
                } else {
                    // Closed path for fill
                    $singleWavePath = $wavePath;
                }
            @endphp
            <path d="{{ $singleWavePath }}" 
                  class="{{ $waveClass }}"
                  style="opacity: {{ $waveOpacity }}; stroke-width: {{ $waveFillStyle === 'stroke' ? '2' : '0' }}px; @if($waveFillStyle === 'fill') fill: {{ $waveColorLight }}; @else stroke: {{ $waveColorLight }}; @endif"
            />
        @endif
    </svg>
</div>
@endif
