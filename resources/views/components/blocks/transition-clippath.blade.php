@props(['data', 'previousBlock' => null, 'nextBlock' => null])

@php
use Happytodev\Blogr\Services\WaveSeparatorService;

$height = $data['height'] ?? 'normal';
$style = $data['clip_style'] ?? 'wavy'; // wavy, zigzag, smooth
$heightPixels = match($height) {
    'short' => 40,
    'tall' => 100,
    default => 60,
};

// Extract colors from adjacent blocks - intelligently based on gradient direction
// Previous block: we want the color at its BOTTOM (where it touches the transition)
// Next block: we want the color at its TOP (where it touches the transition)
$prevColor = WaveSeparatorService::extractEdgeColor($previousBlock, 'bottom');
$nextColor = WaveSeparatorService::extractEdgeColor($nextBlock, 'top');

$fromColor = $prevColor ?? '#667eea';
$toColor = $nextColor ?? '#f093fb';

// Define clip paths
$clipPaths = [
    'wavy' => 'polygon(0 20%, 5% 25%, 10% 20%, 15% 25%, 20% 20%, 25% 25%, 30% 20%, 35% 25%, 40% 20%, 45% 25%, 50% 20%, 55% 25%, 60% 20%, 65% 25%, 70% 20%, 75% 25%, 80% 20%, 85% 25%, 90% 20%, 95% 25%, 100% 20%, 100% 100%, 0 100%)',
    'zigzag' => 'polygon(0 15%, 10% 30%, 20% 15%, 30% 30%, 40% 15%, 50% 30%, 60% 15%, 70% 30%, 80% 15%, 90% 30%, 100% 15%, 100% 100%, 0 100%)',
    'smooth' => 'polygon(0 25%, 100% 0%, 100% 100%, 0 75%)',
];

$clipPath = $clipPaths[$style] ?? $clipPaths['wavy'];
@endphp

{{-- Clip Path Transition Block --}}
<div class="relative w-full overflow-hidden" 
     style="height: {{ $heightPixels }}px; 
             background: linear-gradient(to right, {{ $fromColor }}, {{ $toColor }});
             clip-path: {{ $clipPath }};
             margin: -25px 0;">
</div>
