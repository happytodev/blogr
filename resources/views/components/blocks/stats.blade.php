@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $stats = $data['stats'] ?? [];
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading)
            <h2 class="text-3xl sm:text-4xl font-bold mb-12 text-center">
                {{ $heading }}
            </h2>
        @endif

        @if(count($stats) > 0)
            <div class="grid grid-cols-2 md:grid-cols-{{ min(count($stats), 4) }} gap-8">
                @foreach($stats as $stat)
                    <div 
                        class="text-center"
                        x-data="{ 
                            count: 0, 
                            target: {{ $stat['number'] ?? 0 }},
                            animated: false 
                        }"
                        x-intersect="if (!animated) {
                            animated = true;
                            let duration = 2000;
                            let steps = 60;
                            let increment = target / steps;
                            let current = 0;
                            let interval = setInterval(() => {
                                current += increment;
                                if (current >= target) {
                                    count = target;
                                    clearInterval(interval);
                                } else {
                                    count = Math.floor(current);
                                }
                            }, duration / steps);
                        }"
                    >
                        <div class="text-4xl sm:text-5xl font-bold text-primary-600 dark:text-primary-400 mb-2">
                            <span x-text="count"></span><span>{{ $stat['suffix'] ?? '' }}</span>
                        </div>
                        <p class="font-medium">
                            {{ $stat['label'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
