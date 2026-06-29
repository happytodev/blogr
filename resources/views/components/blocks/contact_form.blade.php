@props(['data'])

@php
    $heading = $data['heading'] ?? __('Send Us a Message');
    $subtitle = $data['subtitle'] ?? __("We'll get back to you within 24 hours.");
    $submitText = $data['submit_text'] ?? __('Send Message');
    $successMessage = $data['success_message'] ?? __('Thank you! Your message has been sent.');
    $toEmail = $data['to_email'] ?? config('blogr.contact.to_email', '');
    $uniqueId = 'contact-form-' . uniqid();

    $image = $data['image'] ?? '';
    $imageAlt = $data['image_alt'] ?? '';
    $imagePosition = $data['image_position'] ?? 'right';
    $imageWidth = (int)($data['image_width'] ?? 50);

    $hasImage = !empty($image);

    if ($hasImage) {
        $gridCols = match($imageWidth) {
            25, 75 => 'lg:grid-cols-4',
            default => 'lg:grid-cols-2',
        };

        $formColSpan = match($imageWidth) {
            25 => 'lg:col-span-3',
            75 => 'lg:col-span-1',
            default => 'lg:col-span-1',
        };

        $imageColSpan = match($imageWidth) {
            25 => 'lg:col-span-1',
            75 => 'lg:col-span-3',
            default => 'lg:col-span-1',
        };
    }

    $imagePath = $image;
    if ($imagePath && !str_starts_with($imagePath, 'storage/')) {
        $imagePath = 'storage/' . $imagePath;
    }
@endphp

<div id="contact-form">
    <x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        @if($heading || $subtitle)
            <div class="text-center mb-10">
                @if($heading)
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
                        {{ $heading }}
                    </h2>
                @endif
                @if($subtitle)
                    <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        @endif

        @if($hasImage)
            <div class="grid grid-cols-1 {{ $gridCols }} gap-8 lg:gap-12">
                @if($imagePosition === 'left')
                    {{-- Image first in DOM → stacks above form on mobile --}}
                    <div class="{{ $imageColSpan }}">
                        <img src="{{ asset($imagePath) }}"
                             alt="{{ $imageAlt }}"
                             class="w-full h-auto rounded-2xl shadow-lg object-cover"
                             loading="lazy">
                    </div>
                    <div class="{{ $formColSpan }}">
                @else
                    {{-- Form first in DOM → stacks above image on mobile --}}
                    <div class="{{ $formColSpan }}">
                @endif
        @endif

        <div id="{{ $uniqueId }}" x-data="contactForm({
            successMessage: '{{ addslashes($successMessage) }}',
            toEmail: '{{ $toEmail }}',
        })" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="{{ $uniqueId }}-name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Your Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="{{ $uniqueId }}-name"
                            type="text"
                            x-model="name"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="{{ __('John Doe') }}"
                        />
                    </div>
                    <div>
                        <label for="{{ $uniqueId }}-email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Your Email') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="{{ $uniqueId }}-email"
                            type="email"
                            x-model="email"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            placeholder="{{ __('john@example.com') }}"
                        />
                    </div>
                </div>

                <div>
                    <label for="{{ $uniqueId }}-subject" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Subject') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="{{ $uniqueId }}-subject"
                        type="text"
                        x-model="subject"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        placeholder="{{ __('How can we help you?') }}"
                    />
                </div>

                <div>
                    <label for="{{ $uniqueId }}-message" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Message') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="{{ $uniqueId }}-message"
                        x-model="message"
                        required
                        rows="5"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-y"
                        placeholder="{{ __('Tell us about your project...') }}"
                    ></textarea>
                </div>

                @stack('contact-form-consent')

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold !text-white shadow-lg transition-all duration-200"
                        :class="loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl active:scale-[0.98]'"
                    >
                        <svg x-show="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading">{{ $submitText }}</span>
                        <span x-show="loading">{{ __('Sending...') }}</span>
                    </button>
                </div>

                <template x-if="submitted">
                    <div class="rounded-xl p-4 text-center" :class="success ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300'">
                        <p class="font-medium" x-text="statusMessage"></p>
                    </div>
                </template>
            </form>
        </div>

        @if($hasImage)
            @if($imagePosition === 'left')
                    </div>
            @else
                    </div>
                    <div class="{{ $imageColSpan }}">
                        <img src="{{ asset($imagePath) }}"
                             alt="{{ $imageAlt }}"
                             class="w-full h-auto rounded-2xl shadow-lg object-cover"
                             loading="lazy">
                    </div>
            @endif
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('alpine:init', function() {
            Alpine.data('contactForm', function(config) {
                return {
                    name: '',
                    email: '',
                    subject: '',
                    message: '',
                    loading: false,
                    submitted: false,
                    success: false,
                    statusMessage: '',
                    submit: function() {
                        var self = this;
                        if (self.loading) return;
                        self.loading = true;
                        self.submitted = false;

                        fetch('{{ route("blogr.cms.contact.submit") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                            body: JSON.stringify({
                                name: self.name,
                                email: self.email,
                                subject: self.subject,
                                message: self.message,
                                to_email: config.toEmail,
                            }),
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success) {
                                self.name = '';
                                self.email = '';
                                self.subject = '';
                                self.message = '';
                                self.success = true;
                                self.statusMessage = config.successMessage;
                            } else {
                                self.success = false;
                                self.statusMessage = data.message || 'An error occurred. Please try again.';
                            }
                            self.submitted = true;
                        })
                        .catch(function() {
                            self.success = false;
                            self.statusMessage = 'Network error. Please try again.';
                            self.submitted = true;
                        })
                        .finally(function() {
                            self.loading = false;
                        });
                    },
                };
            });
        });
    </script>
</x-blogr::background-wrapper>
</div>
