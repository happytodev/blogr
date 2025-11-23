@props(['data', 'previousBlock' => null, 'nextBlock' => null])

@php
use Happytodev\Blogr\Services\WaveSeparatorService;

$height = $data['height'] ?? 'normal';
$heightPixels = match($height) {
    'short' => 30,
    'tall' => 80,
    default => 50,
};

// Extract colors from adjacent blocks - intelligently based on gradient direction
// Previous block: we want the color at its BOTTOM (where it touches the transition)
// Next block: we want the color at its TOP (where it touches the transition)
$prevColor = WaveSeparatorService::extractEdgeColor($previousBlock, 'bottom');
$nextColor = WaveSeparatorService::extractEdgeColor($nextBlock, 'top');

$fromColor = $prevColor ?? '#667eea';
$toColor = $nextColor ?? '#f093fb';
@endphp

{{-- Simple Negative Margin Transition --}}
<div class="relative w-full overflow-hidden" 
     style="height: {{ $heightPixels }}px; 
             background: linear-gradient(to right, {{ $fromColor }}, {{ $toColor }});
             margin: -30px 0;">
</div>
