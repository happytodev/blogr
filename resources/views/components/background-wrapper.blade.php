@props(['data'])

@php
    $backgroundType = $data['background_type'] ?? 'none';
    $backgroundTypeDark = $data['background_type_dark'] ?? null;
    $styles = [];
    $darkStyles = [];
    $classes = ['relative', 'overflow-hidden', 'py-16', 'sm:py-24'];
    
    // Helper function to build pattern SVG
    $buildPatternSvg = function($patternType, $patternColor, $patternSize, $patternSpacing) {
        $tileSize = $patternSpacing;
        $elementSize = $patternSize;
        $center = $tileSize / 2;
        $strokeWidth = max(1, $patternSize / 8);
        $amplitude = $patternSize / 2;
        
        $rgb = sscanf($patternColor, "#%02x%02x%02x");
        $patternColorRgba = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 1)";
        $encodedPatternColor = str_replace(['rgba(', ')', ' ', ','], ['rgba%28', '%29', '', '%2C'], $patternColorRgba);
        
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
        
        return $patterns[$patternType] ?? null;
    };
    
    // Build light mode styles
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
        
        $svg = $buildPatternSvg($patternType, $patternColor, $patternSize, $patternSpacing);
        if ($svg) {
            $styles[] = "background-color: {$backgroundColor}";
            $styles[] = "background-image: url('{$svg}')";
            $styles[] = "background-repeat: repeat";
        }
    }
    
    // Build dark mode styles
    if ($backgroundTypeDark && $backgroundTypeDark !== 'none') {
        if ($backgroundTypeDark === 'color' && isset($data['background_color_dark'])) {
            $opacity = ($data['background_opacity_dark'] ?? 100) / 100;
            $color = $data['background_color_dark'];
            $darkStyles[] = "background-color: {$color}";
            if ($opacity < 1) {
                $darkStyles[] = "opacity: {$opacity}";
            }
        }
        
        if ($backgroundTypeDark === 'gradient' && isset($data['gradient_from_dark'], $data['gradient_to_dark'])) {
            $from = $data['gradient_from_dark'];
            $to = $data['gradient_to_dark'];
            $direction = $data['gradient_direction_dark'] ?? 'to-r';
            $opacity = ($data['background_opacity_dark'] ?? 100) / 100;
            
            $cssDirection = match($direction) {
                'to-r' => 'to right',
                'to-l' => 'to left',
                'to-t' => 'to top',
                'to-b' => 'to bottom',
                'to-br' => 'to bottom right',
                'to-bl' => 'to bottom left',
                default => 'to right',
            };
            
            $darkStyles[] = "background: linear-gradient({$cssDirection}, {$from}, {$to})";
            if ($opacity < 1) {
                $darkStyles[] = "opacity: {$opacity}";
            }
        }
        
        if ($backgroundTypeDark === 'image' && isset($data['background_image_dark'])) {
            $imageUrl = \Storage::disk('public')->url($data['background_image_dark']);
            $size = $data['background_size_dark'] ?? 'cover';
            $position = $data['background_position_dark'] ?? 'center';
            $opacity = ($data['background_opacity_dark'] ?? 100) / 100;
            
            $darkStyles[] = "background-image: url('{$imageUrl}')";
            $darkStyles[] = "background-size: {$size}";
            $darkStyles[] = "background-position: {$position}";
            $darkStyles[] = "background-repeat: no-repeat";
            if ($opacity < 1) {
                $darkStyles[] = "opacity: {$opacity}";
            }
        }
        
        if ($backgroundTypeDark === 'pattern' && isset($data['pattern_type_dark'])) {
            $patternType = $data['pattern_type_dark'];
            $patternColor = $data['pattern_color_dark'] ?? '#e5e7eb';
            $backgroundColor = $data['pattern_background_color_dark'] ?? '#ffffff';
            $patternOpacity = ($data['pattern_opacity_dark'] ?? 100) / 100;
            $patternSize = $data['pattern_size_dark'] ?? 20;
            $patternSpacing = $data['pattern_spacing_dark'] ?? 15;
            
            $svg = $buildPatternSvg($patternType, $patternColor, $patternSize, $patternSpacing);
            if ($svg) {
                $darkStyles[] = "background-color: {$backgroundColor}";
                $darkStyles[] = "background-image: url('{$svg}')";
                $darkStyles[] = "background-repeat: repeat";
            }
        }
    }
    
    $styleAttr = !empty($styles) ? implode('; ', $styles) : '';
    $darkStyleAttr = !empty($darkStyles) ? implode('; ', $darkStyles) : '';
    $uniqueId = 'bg-' . md5(json_encode($data));

    // Text shadow support
    $textShadowEnabled = $data['text_shadow'] ?? false;
    $shadowIntensity = $data['shadow_intensity'] ?? 'medium';
    
    $shadowClasses = '';
    if ($textShadowEnabled) {
        $shadowClasses = match($shadowIntensity) {
            'light' => 'drop-shadow',
            'medium' => 'drop-shadow-lg',
            'heavy' => 'drop-shadow-2xl',
            default => 'drop-shadow-lg',
        };
    }

    // Text colors support
    $headingColor = $data['heading_color'] ?? null;
    $textColor = $data['text_color'] ?? null;
    $subtitleColor = $data['subtitle_color'] ?? null;
    
    $headingColorDark = $data['heading_color_dark'] ?? null;
    $textColorDark = $data['text_color_dark'] ?? null;
    $subtitleColorDark = $data['subtitle_color_dark'] ?? null;
@endphp

<div id="{{ $uniqueId }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }} @if($styleAttr) style="{{ $styleAttr }}" @endif>
    <div class="{{ $shadowClasses }}">
        {{ $slot }}
    </div>
</div>

@if($darkStyleAttr)
    <style>
        .dark #{{ $uniqueId }} {
            {{ $darkStyleAttr }} !important;
        }
    </style>
@endif

@if($headingColor || $textColor || $subtitleColor || $headingColorDark || $textColorDark || $subtitleColorDark)
    <style>
        /* Light mode colors */
        #{{ $uniqueId }} h1,
        #{{ $uniqueId }} h2,
        #{{ $uniqueId }} h3,
        #{{ $uniqueId }} h4,
        #{{ $uniqueId }} h5,
        #{{ $uniqueId }} h6 {
            @if($headingColor) color: {{ $headingColor }} !important; @endif
        }
        
        #{{ $uniqueId }} p,
        #{{ $uniqueId }} li,
        #{{ $uniqueId }} .prose,
        #{{ $uniqueId }} .prose-lg {
            @if($textColor) color: {{ $textColor }} !important; @endif
        }
        
        #{{ $uniqueId }} .subtitle {
            @if($subtitleColor) color: {{ $subtitleColor }} !important; @endif
        }
        
        /* Dark mode colors */
        .dark #{{ $uniqueId }} h1,
        .dark #{{ $uniqueId }} h2,
        .dark #{{ $uniqueId }} h3,
        .dark #{{ $uniqueId }} h4,
        .dark #{{ $uniqueId }} h5,
        .dark #{{ $uniqueId }} h6 {
            @if($headingColorDark) color: {{ $headingColorDark }} !important; @endif
        }
        
        .dark #{{ $uniqueId }} p,
        .dark #{{ $uniqueId }} li,
        .dark #{{ $uniqueId }} .prose,
        .dark #{{ $uniqueId }} .prose-lg {
            @if($textColorDark) color: {{ $textColorDark }} !important; @endif
        }
        
        .dark #{{ $uniqueId }} .subtitle {
            @if($subtitleColorDark) color: {{ $subtitleColorDark }} !important; @endif
        }
    </style>
@endif
