@props(['data', 'previousBlock' => null, 'nextBlock' => null])

@php
use Happytodev\Blogr\Services\WaveSeparatorService;

$height = $data['height'] ?? 'normal';
$animationType = $data['animation_type'] ?? 'fade-slide'; // fade-slide, scale, rotate
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

$animationClass = match($animationType) {
    'scale' => 'transition-animation-scale',
    'rotate' => 'transition-animation-rotate',
    default => 'transition-animation-fade-slide',
};
@endphp

<style>
@keyframes fadeSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scaleY(0.8);
    }
    to {
        opacity: 1;
        transform: scaleY(1);
    }
}

@keyframes rotateIn {
    from {
        opacity: 0;
        transform: rotateX(45deg);
    }
    to {
        opacity: 1;
        transform: rotateX(0);
    }
}

.transition-animation-fade-slide {
    animation: fadeSlideIn 0.8s ease-out forwards;
}

.transition-animation-scale {
    animation: scaleIn 0.8s ease-out forwards;
}

.transition-animation-rotate {
    animation: rotateIn 0.8s ease-out forwards;
}
</style>

{{-- Animated Transition Block --}}
<div class="relative w-full overflow-hidden {{ $animationClass }}" 
     style="height: {{ $heightPixels }}px; 
             background: linear-gradient(to right, {{ $fromColor }}, {{ $toColor }});
             perspective: 1000px;">
</div>
