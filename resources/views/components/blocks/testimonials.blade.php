@props(['data'])

@php
$title = $data['title'] ?? '';
$items = $data['items'] ?? [];
$fullWidth = $data['full_width'] ?? false;

// Determine grid classes and text sizes based on full_width option
$gridClasses = $fullWidth 
    ? 'grid grid-cols-1 gap-8 max-w-4xl mx-auto' 
    : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8';

$quoteTextSize = $fullWidth ? 'text-xl' : 'text-gray-700';
$cardPadding = $fullWidth ? 'p-10' : 'p-8';
@endphp

<x-blogr::background-wrapper :data="$data" class="py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <h2 class="text-3xl sm:text-4xl font-bold mb-16 text-center">
                {{ $title }}
            </h2>
        @endif
        
        <div class="{{ $gridClasses }}">
            @foreach($items as $item)
                <div class="bg-white dark:bg-gray-900 rounded-xl {{ $cardPadding }} shadow-lg">
                    <div class="flex items-center mb-4">
                        @if(!empty($item['photo']))
                            <img src="{{ asset('storage/' . $item['photo']) }}" 
                                 alt="{{ $item['name'] }}" 
                                 class="{{ $fullWidth ? 'w-20 h-20' : 'w-16 h-16' }} rounded-full object-cover mr-4">
                        @endif
                        <div>
                            <h3 class="font-bold {{ $fullWidth ? 'text-xl' : '' }}">
                                {{ $item['name'] }}
                            </h3>
                            @if(!empty($item['role']))
                                <p class="subtitle text-sm">
                                    {{ $item['role'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    <blockquote class="{{ $quoteTextSize }} dark:text-gray-300 mb-4">
                        "{{ $item['quote'] }}"
                    </blockquote>
                    
                    @if(!empty($item['rating']) && $item['rating'] > 0)
                        <div class="flex items-center">
                            @for($i = 1; $i <= $item['rating']; $i++)
                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-blogr::background-wrapper>
