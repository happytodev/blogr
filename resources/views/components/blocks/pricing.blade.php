@props(['data'])

@php
    use Happytodev\Blogr\Helpers\LinkResolver;
    
    $heading = $data['heading'] ?? null;
    $description = $data['description'] ?? null;
    $plans = $data['plans'] ?? [];
    $columns = $data['columns'] ?? '3';
    $showYearly = $data['show_yearly_toggle'] ?? false;
    $gridClass = match($columns) {
        '2' => 'grid-cols-1 md:grid-cols-2',
        '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    };
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading || $description)
            <div class="text-center mb-12">
                @if($heading)
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                        {{ $heading }}
                    </h2>
                @endif
                
                @if($description)
                    <p class="text-xl">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        @if(count($plans) > 0)
            <div x-data="{ yearly: false }">
                @if($showYearly)
                    <div class="flex justify-center mb-10">
                        <div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-full p-1 shadow-inner">
                            <button @click="yearly = false"
                                    :class="!yearly ? 'bg-white dark:bg-gray-600 shadow-sm' : 'bg-transparent hover:bg-gray-200 dark:hover:bg-gray-700'"
                                    class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-200">
                                {{ __('Monthly') }}
                            </button>
                            <button @click="yearly = true"
                                    :class="yearly ? 'bg-white dark:bg-gray-600 shadow-sm' : 'bg-transparent hover:bg-gray-200 dark:hover:bg-gray-700'"
                                    class="px-5 py-2 rounded-full text-sm font-medium transition-all duration-200">
                                {{ __('Yearly') }}
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid {{ $gridClass }} gap-8 max-w-5xl mx-auto">
                    @foreach($plans as $plan)
                        @php
                            $monthlyPrice = $plan['price'] ?? 0;

                            // Backward compat: old yearly_discount = percent
                            $discountType = $plan['yearly_discount_type']
                                ?? (isset($plan['yearly_discount']) ? 'percent' : null);
                            $discountValue = $plan['yearly_discount_value']
                                ?? $plan['yearly_discount']
                                ?? null;

                            $yearlyTotal = round($monthlyPrice * 12);
                            $saveLabel = null;

                            if ($discountType && $discountValue !== null) {
                                switch ($discountType) {
                                    case 'percent':
                                        $yearlyTotal = round($monthlyPrice * 12 * (1 - $discountValue / 100));
                                        $saveLabel = "Save {$discountValue}%";
                                        break;
                                    case 'fixed':
                                        $yearlyTotal = round(max(0, $monthlyPrice * 12 - $discountValue));
                                        $saveLabel = "\${$discountValue} off";
                                        break;
                                    case 'months':
                                        $paidMonths = max(1, 12 - (int) $discountValue);
                                        $yearlyTotal = round($monthlyPrice * $paidMonths);
                                        $saveLabel = $discountValue > 1
                                            ? "{$discountValue} months free"
                                            : "1 month free";
                                        break;
                                }
                            }
                        @endphp

                        <div class="relative flex flex-col rounded-2xl border-2 {{ !empty($plan['highlight']) ? 'border-[var(--color-primary)] shadow-xl scale-105' : 'border-gray-200 dark:border-gray-700' }} bg-white dark:bg-gray-800 p-8">
                            @if(!empty($plan['highlight']))
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[var(--color-primary)] text-white shadow-lg">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ __('Popular') }}
                                    </span>
                                </div>
                            @endif

                            <!-- Header -->
                            <div class="mb-6">
                                <h3 class="text-2xl font-bold mb-2">
                                    {{ $plan['name'] ?? 'Plan' }}
                                </h3>
                                
                                @if(!empty($plan['description']))
                                    <p class="text-sm">
                                        {{ $plan['description'] }}
                                    </p>
                                @endif
                            </div>

                            <!-- Price -->
                            <div class="mb-6">
                                <div x-show="{{ $showYearly ? '!yearly' : 'true' }}" class="flex items-baseline">
                                    <span class="text-5xl font-bold">
                                        ${{ $plan['price'] ?? '0' }}
                                    </span>
                                    @if(!empty($plan['period']))
                                        <p class="ml-2">
                                            {{ match($plan['period']) {
                                                'month' => '/ month',
                                                'year' => '/ year',
                                                'once' => 'one-time',
                                                default => $plan['period']
                                            } }}
                                        </p>
                                    @endif
                                </div>

                                @if($showYearly)
                                    <div x-show="yearly">
                                        <div class="flex items-baseline">
                                            <span class="text-5xl font-bold">${{ $yearlyTotal }}</span>
                                            <span class="ml-2">/ year</span>
                                        </div>
                                        @if($saveLabel)
                                            <div class="mt-1.5">
                                                <span class="text-sm line-through text-gray-400">${{ round($monthlyPrice * 12) }}</span>
                                                <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                                    {{ $saveLabel }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Features -->
                            @if(!empty($plan['features']) && count($plan['features']) > 0)
                                <ul class="space-y-4 mb-8 flex-grow">
                                    @foreach($plan['features'] as $feature)
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-[var(--color-primary)] mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span>
                                                {{ is_array($feature) ? $feature['feature'] ?? '' : $feature }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <!-- CTA Button -->
                            @php
                                $planCta = LinkResolver::resolve($plan, 'cta_link_type', 'cta_url', 'cta_category_id', 'cta_cms_page_id') ?? '#';
                            @endphp
                            <a 
                                href="{{ $planCta }}"
                                class="w-full py-3 px-6 text-center rounded-lg font-semibold transition-all duration-200 {{ !empty($plan['highlight']) ? 'bg-[var(--color-primary)] text-white hover:brightness-110' : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600' }}"
                            >
                                {{ $plan['cta_text'] ?? __('Get Started') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
