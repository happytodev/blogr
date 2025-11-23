@props(['data', 'previousBlock' => null, 'nextBlock' => null])

@php
use Happytodev\Blogr\Services\WaveSeparatorService;

// Configuration basics
$position = $data['position'] ?? 'bottom';
$height = $data['height'] ?? 'normal';
$waveMode = $data['wave_mode'] ?? 'auto';

// Height in pixels
$heightPixels = match($height) {
    'short' => 60,
    'tall' => 150,
    default => 100,
};

// Calculate wave configuration based on mode
$waveConfig = WaveSeparatorService::calculateWaveConfig(
    previousBlock: $previousBlock,
    nextBlock: $nextBlock,
    mode: $waveMode,
    manualConfig: $data
);

// Get wave paths
$wavePaths = WaveSeparatorService::getWavePaths();
$waveStyle = $waveConfig['waveStyle'] ?? 'wave-3';
$waveAmplitude = $waveConfig['waveAmplitude'] ?? 'medium';
$wavePath = $wavePaths[$waveStyle] ?? $wavePaths['wave-3'];

// Adjust amplitude
$wavePath = WaveSeparatorService::adjustWaveAmplitude($wavePath, $waveAmplitude);

// Get primary colors from first layer
$colorLight = $waveConfig['layers'][0]['colorLight'] ?? '#d946ef';
$colorDark = $waveConfig['layers'][0]['colorDark'] ?? '#ec4899';
@endphp

{{-- Clean Wave Separator Block with intelligent color transitions --}}
<div class="relative w-full overflow-hidden" style="height: {{ $heightPixels }}px; background: linear-gradient(to right, {{ $colorLight }}, {{ $colorDark }});">
    
    {{-- Top wave (if position includes top) --}}
    @if(in_array($position, ['top', 'both']))
        <svg class="absolute top-0 left-0 w-full h-full" 
             viewBox="0 0 1440 120" 
             preserveAspectRatio="none" 
             xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="waveGradientTop" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:{{ $colorLight }}" />
                    <stop offset="100%" style="stop-color:{{ $colorDark }}" />
                </linearGradient>
            </defs>
            <path d="{{ $wavePath }}" fill="url(#waveGradientTop)" />
        </svg>
    @endif

    {{-- Bottom wave (if position includes bottom) --}}
    @if(in_array($position, ['bottom', 'both']))
        <svg class="absolute bottom-0 left-0 w-full h-full" 
             viewBox="0 0 1440 120" 
             preserveAspectRatio="none" 
             xmlns="http://www.w3.org/2000/svg"
             style="transform: scaleY(-1);">
            <defs>
                <linearGradient id="waveGradientBottom" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:{{ $colorLight }}" />
                    <stop offset="100%" style="stop-color:{{ $colorDark }}" />
                </linearGradient>
            </defs>
            <path d="{{ $wavePath }}" fill="url(#waveGradientBottom)" />
        </svg>
    @endif
</div>
