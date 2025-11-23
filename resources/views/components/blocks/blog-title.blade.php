@props(['data'])

@php
$title = $data['title'] ?? __('Blog');
$description = $data['description'] ?? '';
$enabled = $data['enabled'] ?? true;
$paddingTop = $data['padding_top'] ?? 40;
$paddingBottom = $data['padding_bottom'] ?? 40;
$textAlignment = $data['text_alignment'] ?? 'center';

if (!$enabled) {
    return;
}

$alignmentClass = match($textAlignment) {
    'left' => 'text-left',
    'right' => 'text-right',
    default => 'text-center',
};
@endphp

<x-blogr::background-wrapper :data="$data" class="py-0">
    <div style="padding-top: {{ $paddingTop }}px; padding-bottom: {{ $paddingBottom }}px;" class="{{ $alignmentClass }}">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-5xl sm:text-6xl font-bold leading-tight mb-4">
                {{ $title }}
            </h1>

            @if($description)
                <p class="text-xl sm:text-2xl opacity-90 max-w-2xl{{ $textAlignment === 'center' ? ' mx-auto' : '' }}">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
</x-blogr::background-wrapper>
