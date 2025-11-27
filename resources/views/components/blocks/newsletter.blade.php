@props(['data'])

@php
    $heading = $data['heading'] ?? '';
    $description = $data['description'] ?? '';
    $placeholder = $data['placeholder'] ?? __('Enter your email');
    $buttonText = $data['button_text'] ?? __('Subscribe');
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">
            {{ $heading }}
        </h2>
        
        @if($description)
            <p class="subtitle text-lg mb-8">
                {{ $description }}
            </p>
        @endif

        <form 
            action="#" 
            method="POST" 
            class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto"
            x-data="{ 
                email: '', 
                loading: false,
                success: false,
                error: false
            }"
            @submit.prevent="
                loading = true;
                error = false;
                // Simulate API call - replace with actual endpoint
                setTimeout(() => {
                    loading = false;
                    success = true;
                    email = '';
                    setTimeout(() => { success = false; }, 3000);
                }, 1000);
            "
        >
            @csrf
            <div class="flex-1">
                <input 
                    type="email" 
                    name="email"
                    x-model="email"
                    placeholder="{{ $placeholder }}"
                    required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent"
                >
            </div>
            <button 
                type="submit"
                :disabled="loading"
                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
            >
                <span x-show="!loading && !success">{{ $buttonText }}</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('Loading...') }}
                </span>
                <span x-show="success">✓ {{ __('Subscribed!') }}</span>
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ __('We respect your privacy. Unsubscribe at any time.') }}
        </p>
    </div>
</x-blogr::background-wrapper>
