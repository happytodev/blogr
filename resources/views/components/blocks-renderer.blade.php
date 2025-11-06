@props(['blocks'])

@php
$blocksData = is_string($blocks) ? json_decode($blocks, true) : $blocks;
$blocksData = $blocksData ?? [];
@endphp

@foreach($blocksData as $block)
    @php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];
    $componentView = "blogr::components.blocks.{$type}";
    @endphp
    
    @if($type && view()->exists($componentView))
        @include($componentView, ['data' => $data])
    @endif
@endforeach
