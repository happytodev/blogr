@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $events = $data['events'] ?? [];
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading)
            <h2 class="text-3xl sm:text-4xl font-bold mb-12 text-center">
                {{ $heading }}
            </h2>
        @endif

        @if(count($events) > 0)
            <div class="relative border-l-2 border-primary-600 dark:border-primary-400 pl-8 space-y-8">
                @foreach($events as $event)
                    <div class="relative">
                        <div class="absolute -left-10 mt-1.5 w-4 h-4 rounded-full bg-primary-600 dark:bg-primary-400 border-4 border-white dark:border-gray-800"></div>
                        
                        <div class="text-sm font-semibold text-primary-600 dark:text-primary-400 mb-1">
                            {{ $event['date'] ?? '' }}
                        </div>
                        
                        <h3 class="text-xl font-bold mb-2">
                            {{ $event['title'] }}
                        </h3>
                        
                        @if(!empty($event['description']))
                            <p>
                                {{ $event['description'] }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
