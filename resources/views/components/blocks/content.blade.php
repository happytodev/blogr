@props(['data'])

@php
$content = $data['content'] ?? '';
$maxWidth = $data['max_width'] ?? 'prose';
$containerClass = $maxWidth === 'full' ? 'max-w-full' : 'max-w-prose';
@endphp

<x-blogr::background-wrapper :data="$data" class="py-16 sm:py-20">
    <div class="{{ $containerClass }} mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-a:text-[var(--color-primary)] dark:prose-a:text-[var(--color-primary-dark)] max-w-none">
            {!! \Illuminate\Support\Str::markdown($content) !!}
        </div>
    </div>
</x-blogr::background-wrapper>
