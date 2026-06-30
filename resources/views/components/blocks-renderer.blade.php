@props(['blocks'])

@php
$blocksData = is_string($blocks) ? json_decode($blocks, true) : $blocks;
$blocksData = $blocksData ?? [];
@endphp

@foreach($blocksData as $index => $block)
    @php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];
    $isHidden = $data['hidden'] ?? false;
    @endphp

    @if($isHidden)
        @continue
    @endif

    @php
    $componentView = "blogr::components.blocks.{$type}";
    $isTransitionBlock = str_starts_with($type, 'transition-');
    
    // Detect adjacent blocks for intelligent color calculations
    // Use array position lookup to handle UUID and numeric keys safely
    $keys = array_keys($blocksData);
    $pos = array_search($index, $keys, true);
    $previousBlock = ($pos !== false && $pos > 0) ? $blocksData[$keys[$pos - 1]] ?? null : null;
    $nextBlock = ($pos !== false && $pos < count($keys) - 1) ? $blocksData[$keys[$pos + 1]] ?? null : null;
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
