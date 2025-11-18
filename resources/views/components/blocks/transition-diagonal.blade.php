@props(['data', 'previousBlock' => null, 'nextBlock' => null])

@php
use Happytodev\Blogr\Services\WaveSeparatorService;

// Shape selection: wavy, zigzag, diagonal, smooth
$shape = $data['shape'] ?? 'wavy';
$amplitude = intval($data['amplitude'] ?? 40);
$diagonalDirection = $data['diagonal_direction'] ?? 'left'; // 'left' or 'right'

// Extract color from previous block for top part
$prevColor = WaveSeparatorService::extractEdgeColor($previousBlock, 'bottom');
$prevColor = $prevColor ?? '#667eea'; // Default fallback

// Only use colors from solid backgrounds, not gradients
$nextColor = WaveSeparatorService::extractEdgeColor($nextBlock, 'bottom');

// If next block has no background (none), inherit from previous block
// Otherwise use the next block color for smooth transition
$transitionColor = $nextColor ?? $prevColor;

// DARK MODE SUPPORT
// Extract dark mode colors if available
$prevColorDark = null;
$nextColorDark = null;
$transitionColorDark = null;

if (!empty($nextBlock) && is_array($nextBlock)) {
    $data_next = $nextBlock['data'] ?? [];
    $backgroundTypeDark = $data_next['background_type_dark'] ?? null;
    
    if ($backgroundTypeDark === 'color' && isset($data_next['background_color_dark'])) {
        $nextColorDark = $data_next['background_color_dark'];
    }
}

if (!empty($previousBlock) && is_array($previousBlock)) {
    $data_prev = $previousBlock['data'] ?? [];
    $backgroundTypeDark = $data_prev['background_type_dark'] ?? null;
    
    if ($backgroundTypeDark === 'color' && isset($data_prev['background_color_dark'])) {
        $prevColorDark = $data_prev['background_color_dark'];
    }
}

// For transition color in dark mode: prefer next block dark color, then fallback to next block light color
$transitionColorDark = $nextColorDark ?? $nextColor ?? $prevColorDark ?? $transitionColor;

// Constrain amplitude to viewBox (0-120 range)
$amp = min($amplitude, 100);

// Height in pixels - scale based on amplitude
$heightPixels = max(50, intval(50 + ($amp * 0.6)));

// Sinusoidal wave calculation with true sine function
// Center point in viewBox (always 60 to keep it centered)
$centerY = 60;

// Amplitude controls the deviation from center - it never exceeds viewBox height
// Amplitude is normalized so max is 60px (leaving 0-120 range intact)
$normalizedAmplitude = min($amp, 100) / 100 * 50; // Scale 0-100 to 0-50px deviation

// Frequency - more waves for higher amplitude creates smoother appearance
$frequency = max(2, intval($amp / 20)); // More waves = smoother for larger amplitudes

// Build sinusoidal path using quadratic curves to approximate sine
$wavyPath = "M0,{$centerY}";
$pathPoints = [];

// Generate smooth sinusoidal curve across entire viewBox width
for ($i = 0; $i <= 1440; $i += 60) {
    // Sine wave: sin(x) gives -1 to 1, multiply by amplitude to get deviation
    $angle = ($i / 1440) * $frequency * M_PI * 2; // Full sine cycles based on frequency
    $deviation = sin($angle) * $normalizedAmplitude;
    $y = intval($centerY + $deviation);
    $pathPoints[] = "{$i},{$y}";
}

// Connect points with quadratic curves for smooth appearance
$wavyPath = "M" . $pathPoints[0];
for ($i = 1; $i < count($pathPoints); $i++) {
    $wavyPath .= " L" . $pathPoints[$i];
}

// Close the path to fill
$wavyPath .= " L1440,120 L0,120 Z";

// Calculate peak and trough for other shapes (using same sinusoidal logic)
$peak = intval($centerY - $normalizedAmplitude);
$trough = intval($centerY + $normalizedAmplitude);

// Define SVG paths based on shape - all paths fit within viewBox 0-120
// The shape BOTTOM edge shows the next block color, TOP edge shows previous block (transparent/visible)

// INVERSE paths: fill from top (Y=0) down to the wave line - shows previous block color
$wavyPathInverse = "M0,0 L" . $pathPoints[0] . " ";
for ($i = 1; $i < count($pathPoints); $i++) {
    $wavyPathInverse .= "L" . $pathPoints[$i] . " ";
}
$wavyPathInverse .= "L1440,0 Z";

$diagonalPathInverse = ($diagonalDirection === 'right') 
    ? "M0,0 L0,{$peak} L1440,{$trough} L1440,0 Z"  // Right to left (inverted)
    : "M0,0 L0,{$trough} L1440,{$peak} L1440,0 Z"; // Left to right (normal)

$pathsInverse = [
    'wavy' => $wavyPathInverse,
    
    // Zigzag: sharp angular cuts - fill from top down to zigzag
    'zigzag' => "M0,0 L0,{$trough} L180,{$peak} L360,{$trough} L540,{$peak} L720,{$trough} L900,{$peak} L1080,{$trough} L1260,{$peak} L1440,{$trough} L1440,0 Z",
    
    // Diagonal: simple angle (with direction option)
    'diagonal' => $diagonalPathInverse,
    
    // Smooth: organic curved path with cubic bezier for better fluidity
    'smooth' => "M0,0 L0,{$trough} C240,{$peak} 480,{$peak} 720,{$trough} C960,{$peak} 1200,{$peak} 1440,{$trough} L1440,0 Z",
];

$diagonalPath = ($diagonalDirection === 'right') 
    ? "M0,{$peak} L1440,{$trough} L1440,120 L0,120 Z"  // Right to left (inverted)
    : "M0,{$trough} L1440,{$peak} L1440,120 L0,120 Z"; // Left to right (normal)

$paths = [
    'wavy' => $wavyPath,
    
    // Zigzag: sharp angular cuts
    'zigzag' => "M0,{$trough} L180,{$peak} L360,{$trough} L540,{$peak} L720,{$trough} L900,{$peak} L1080,{$trough} L1260,{$peak} L1440,{$trough} L1440,120 L0,120 Z",
    
    // Diagonal: simple angle (with direction option)
    'diagonal' => $diagonalPath,
    
    // Smooth: organic curved path with cubic bezier for better fluidity
    'smooth' => "M0,{$trough} C240,{$peak} 480,{$peak} 720,{$trough} C960,{$peak} 1200,{$peak} 1440,{$trough} L1440,120 L0,120 Z",
];

$svgPath = $paths[$shape] ?? $paths['wavy'];
$svgPathInverse = $pathsInverse[$shape] ?? $pathsInverse['wavy'];

$svgPath = $paths[$shape] ?? $paths['wavy'];
$uniqueId = 'wave-' . md5(json_encode($data));
@endphp

{{-- SVG Transition Shape with Background Color from Next Block --}}
{{-- SVG Transition Shape with Background Color from Next Block --}}
<div id="{{ $uniqueId }}" class="relative w-full -mt-16 sm:-mt-20 2xl:-mt-12" style="background-color: transparent; pointer-events: none; height: {{ $heightPixels }}px;">
    <svg class="w-full absolute bottom-0 left-0 z-50" style="display: block; margin-bottom: -1px;" 
         viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path fill="{{ $transitionColor }}" d="{{ $svgPath }}" opacity="1"/>
    </svg>
</div>

@if($transitionColorDark !== $transitionColor)
    <style>
        .dark #{{ $uniqueId }} path {
            fill: {{ $transitionColorDark }} !important;
        }
    </style>
@endif
