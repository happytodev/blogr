@props(['data'])

@php
    $backgroundType = $data['background_type'] ?? 'none';
    $styles = [];
    $classes = ['relative', 'overflow-hidden', 'py-16', 'sm:py-24'];
    
    // Build inline styles based on background type
    if ($backgroundType === 'color' && isset($data['background_color'])) {
        $opacity = ($data['background_opacity'] ?? 100) / 100;
        $color = $data['background_color'];
        $styles[] = "background-color: {$color}";
        if ($opacity < 1) {
            $styles[] = "opacity: {$opacity}";
        }
    }
    
    if ($backgroundType === 'gradient' && isset($data['gradient_from'], $data['gradient_to'])) {
        $from = $data['gradient_from'];
        $to = $data['gradient_to'];
        $direction = $data['gradient_direction'] ?? 'to-r';
        $opacity = ($data['background_opacity'] ?? 100) / 100;
        
        // Map Tailwind direction to CSS gradient direction
        $cssDirection = match($direction) {
            'to-r' => 'to right',
            'to-l' => 'to left',
            'to-t' => 'to top',
            'to-b' => 'to bottom',
            'to-br' => 'to bottom right',
            'to-bl' => 'to bottom left',
            default => 'to right',
        };
        
        $styles[] = "background: linear-gradient({$cssDirection}, {$from}, {$to})";
        if ($opacity < 1) {
            $styles[] = "opacity: {$opacity}";
        }
    }
    
    if ($backgroundType === 'image' && isset($data['background_image'])) {
        $imageUrl = \Storage::disk('public')->url($data['background_image']);
        $size = $data['background_size'] ?? 'cover';
        $position = $data['background_position'] ?? 'center';
        $opacity = ($data['background_opacity'] ?? 100) / 100;
        
        $styles[] = "background-image: url('{$imageUrl}')";
        $styles[] = "background-size: {$size}";
        $styles[] = "background-position: {$position}";
        $styles[] = "background-repeat: no-repeat";
        if ($opacity < 1) {
            $styles[] = "opacity: {$opacity}";
        }
    }
    
    if ($backgroundType === 'pattern' && isset($data['pattern_type'])) {
        $patternType = $data['pattern_type'];
        $patternColor = $data['pattern_color'] ?? '#e5e7eb';
        $backgroundColor = $data['pattern_background_color'] ?? '#ffffff';
        $patternOpacity = ($data['pattern_opacity'] ?? 100) / 100;
        $patternSize = $data['pattern_size'] ?? 20;
        $patternSpacing = $data['pattern_spacing'] ?? 15;
        $patternRotation = $data['pattern_rotation'] ?? 0;
        
        // Convert hex color to RGB for opacity support
        $rgb = sscanf($patternColor, "#%02x%02x%02x");
        $patternColorRgba = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, {$patternOpacity})";
        
        // Encode colors for URL (# becomes %23, rgba needs special encoding)
        $encodedPatternColor = str_replace(['rgba(', ')', ' ', ','], ['rgba%28', '%29', '', '%2C'], $patternColorRgba);
        
        // Calculate dimensions based on BOTH size and spacing
        $tileSize = $patternSpacing;
        $elementSize = $patternSize; // Use patternSize directly for element dimensions
        $center = $tileSize / 2;
        $strokeWidth = max(1, $patternSize / 8); // Stroke width based on pattern size
        
        // For waves and zigzag, use patternSize to control amplitude
        $amplitude = $patternSize / 2; // Half of pattern size for wave height
        
        // SVG patterns with dynamic sizing
        // Use %22 for quotes to avoid conflicts with HTML attributes
        $patterns = [
            'dots' => "data:image/svg+xml,%3Csvg width=%22{$tileSize}%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Ccircle cx=%22{$center}%22 cy=%22{$center}%22 r=%22" . min($elementSize / 2, $tileSize * 0.4) . "%22 fill=%22{$encodedPatternColor}%22/%3E%3C/svg%3E",
            
            'grid' => "data:image/svg+xml,%3Csvg width=%22{$tileSize}%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M0 0L{$tileSize} 0L{$tileSize} {$tileSize}L0 {$tileSize}Z%22 fill=%22none%22 stroke=%22{$encodedPatternColor}%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
            
            'stripes' => "data:image/svg+xml,%3Csvg width=%22{$tileSize}%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M0 0L{$tileSize} {$tileSize}M{$tileSize} 0L0 {$tileSize}%22 stroke=%22{$encodedPatternColor}%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
            
            'waves' => "data:image/svg+xml,%3Csvg width=%22" . ($tileSize * 2) . "%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M0 {$center} Q " . ($tileSize / 2) . " " . ($center - $amplitude) . ", {$tileSize} {$center} T " . ($tileSize * 2) . " {$center}%22 stroke=%22{$encodedPatternColor}%22 fill=%22none%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
            
            'circles' => "data:image/svg+xml,%3Csvg width=%22{$tileSize}%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Ccircle cx=%22{$center}%22 cy=%22{$center}%22 r=%22" . min($elementSize / 2, $tileSize * 0.4) . "%22 fill=%22none%22 stroke=%22{$encodedPatternColor}%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
            
            'zigzag' => "data:image/svg+xml,%3Csvg width=%22{$tileSize}%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M0 {$center} L" . ($tileSize / 2) . " " . ($center - $amplitude) . " L{$tileSize} {$center} L" . ($tileSize / 2) . " " . ($center + $amplitude) . " Z%22 fill=%22none%22 stroke=%22{$encodedPatternColor}%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
            
            'cross' => "data:image/svg+xml,%3Csvg width=%22{$tileSize}%22 height=%22{$tileSize}%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M{$center} 0L{$center} {$tileSize}M0 {$center}L{$tileSize} {$center}%22 stroke=%22{$encodedPatternColor}%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
            
            'hexagons' => "data:image/svg+xml,%3Csvg width=%22" . ($tileSize * 1.5) . "%22 height=%22" . ($tileSize * 1.732) . "%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M" . ($tileSize * 0.75) . " 0 L" . ($tileSize * 1.5) . " " . ($tileSize * 0.433) . " L" . ($tileSize * 1.5) . " " . ($tileSize * 1.299) . " L" . ($tileSize * 0.75) . " " . ($tileSize * 1.732) . " L0 " . ($tileSize * 1.299) . " L0 " . ($tileSize * 0.433) . " Z%22 fill=%22none%22 stroke=%22{$encodedPatternColor}%22 stroke-width=%22{$strokeWidth}%22/%3E%3C/svg%3E",
        ];
        
        if (isset($patterns[$patternType])) {
            // Apply pattern directly to element
            $styles[] = "background-color: {$backgroundColor}";
            $styles[] = "background-image: url('{$patterns[$patternType]}')";
            $styles[] = "background-repeat: repeat";
        }
    }
    
    $styleAttr = !empty($styles) ? implode('; ', $styles) : '';
    $uniqueId = 'bg-' . md5(json_encode($data));
@endphp

<div id="{{ $uniqueId }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }} @if($styleAttr) style="{{ $styleAttr }}" @endif>
    {{ $slot }}
</div>
