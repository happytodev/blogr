@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $description = $data['description'] ?? null;
    $plans = $data['plans'] ?? [];
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading || $description)
            <div class="text-center mb-12">
                @if($heading)
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ $heading }}
                    </h2>
                @endif
                
                @if($description)
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        @if(count($plans) > 0)
            <div class="grid grid-cols-1 md:grid-cols-{{ min(count($plans), 3) }} gap-8 max-w-5xl mx-auto">
                @foreach($plans as $plan)
                    <div class="relative flex flex-col rounded-2xl border-2 {{ !empty($plan['highlight']) ? 'border-primary-600 shadow-xl scale-105' : 'border-gray-200 dark:border-gray-700' }} bg-white dark:bg-gray-800 p-8">
                        @if(!empty($plan['highlight']))
                            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                <span class="inline-flex items-center px-4 py-1 rounded-full text-sm font-semibold bg-primary-600 text-white">
                                    {{ __('Popular') }}
                                </span>
                            </div>
                        @endif

                        <!-- Header -->
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $plan['name'] ?? 'Plan' }}
                            </h3>
                            
                            @if(!empty($plan['description']))
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    {{ $plan['description'] }}
                                </p>
                            @endif
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <div class="flex items-baseline">
                                <span class="text-5xl font-bold text-gray-900 dark:text-white">
                                    ${{ $plan['price'] ?? '0' }}
                                </span>
                                @if(!empty($plan['period']))
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">
                                        {{ match($plan['period']) {
                                            'month' => '/ month',
                                            'year' => '/ year',
                                            'once' => 'one-time',
                                            default => $plan['period']
                                        } }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Features -->
                        @if(!empty($plan['features']) && count($plan['features']) > 0)
                            <ul class="space-y-4 mb-8 flex-grow">
                                @foreach($plan['features'] as $feature)
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ is_array($feature) ? $feature['feature'] ?? '' : $feature }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <!-- CTA Button -->
                        <a 
                            href="{{ $plan['cta_url'] ?? '#' }}"
                            class="w-full py-3 px-6 text-center rounded-lg font-semibold transition-all duration-200 {{ !empty($plan['highlight']) ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600' }}"
                        >
                            {{ $plan['cta_text'] ?? __('Get Started') }}
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
