@props(['blocks'])

@php
$blocksData = is_string($blocks) ? json_decode($blocks, true) : $blocks;
$blocksData = $blocksData ?? [];
@endphp

@foreach($blocksData as $index => $block)
    @php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];
    $componentView = "blogr::components.blocks.{$type}";
    $isTransitionBlock = str_starts_with($type, 'transition-');
    
    // Detect adjacent blocks for intelligent color calculations
    // Use intval to protect against non-numeric string keys (e.g., associative array)
    $intIndex = is_numeric($index) ? (int) $index : -1;
    $previousBlock = ($intIndex > 0) ? $blocksData[$intIndex - 1] : null;
    $nextBlock = ($intIndex >= 0 && $intIndex < count($blocksData) - 1) ? $blocksData[$intIndex + 1] : null;
    @endphp
    
    @if($type && view()->exists($componentView))
        @if($isTransitionBlock)
            {{-- Transition blocks positioned smoothly between blocks without overlap --}}
            @include($componentView, [
                'data' => $data,
                'previousBlock' => $previousBlock,
                'nextBlock' => $nextBlock
            ])
        @else
            @include($componentView, ['data' => $data])
        @endif
    @endif
@endforeach
