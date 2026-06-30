@props(['data'])

@php
    $heading = $data['heading'] ?? __('Send Us a Message');
    $subtitle = $data['subtitle'] ?? __("We'll get back to you within 24 hours.");
    $submitText = $data['submit_text'] ?? __('Send Message');
    $successMessage = $data['success_message'] ?? __('Thank you! Your message has been sent.');
    $toEmail = $data['to_email'] ?? config('blogr.contact.to_email', '');
    $uniqueId = 'contact-form-' . uniqid();

    $image = $data['image'] ?? '';
    if (is_array($image)) {
        $image = !empty($image) ? (string) reset($image) : '';
    }

    $imageAlt = $data['image_alt'] ?? '';
    $imagePosition = $data['image_position'] ?? 'right';
    $imageWidth = (int)($data['image_width'] ?? 50);

    $hasImage = !empty($image);

    $formBgColor = $data['form_background_color'] ?? null;
    $formBgColorDark = $data['form_background_color_dark'] ?? null;
    $buttonColor = $data['button_color'] ?? '#4f46e5';
    $buttonColorDark = $data['button_color_dark'] ?? '#4f46e5';

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
                    <div class="{{ $imageColSpan }}">
                        <img src="{{ asset($imagePath) }}"
                             alt="{{ $imageAlt }}"
                             class="w-full h-auto rounded-2xl shadow-lg object-cover"
                             loading="lazy">
                    </div>
                    <div class="{{ $formColSpan }}">
                @else
                    <div class="{{ $formColSpan }}">
                @endif
        @endif

        <div id="{{ $uniqueId }}" x-data="{
            name: '',
            email: '',
            subject: '',
            message: '',
            loading: false,
            submitted: false,
            success: false,
            statusMessage: '',
            successMessage: '{{ addslashes($successMessage) }}',
            toEmail: '{{ $toEmail }}',
            gdprConsent: false,
            consentTouched: false,
            submit() {
                if (this.loading) return;
                this.loading = true;
                this.submitted = false;

                fetch('{{ route("blogr.cms.contact.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        name: this.name,
                        email: this.email,
                        subject: this.subject,
                        message: this.message,
                        to_email: this.toEmail,
                    }),
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        this.name = '';
                        this.email = '';
                        this.subject = '';
                        this.message = '';
                        this.success = true;
                        this.statusMessage = this.successMessage;
                    } else {
                        this.success = false;
                        this.statusMessage = data.message || 'An error occurred. Please try again.';
                    }
                    this.submitted = true;
                }.bind(this))
                .catch(function() {
                    this.success = false;
                    this.statusMessage = 'Network error. Please try again.';
                    this.submitted = true;
                }.bind(this))
                .finally(function() {
                    this.loading = false;
                }.bind(this));
            },
        }" class="rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 sm:p-8"
             @if($formBgColor) style="background-color: {{ $formBgColor }};" @endif>
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
                        :class="loading ? 'bg-gray-400 cursor-not-allowed' : 'hover:shadow-xl active:scale-[0.98]'"
                        style="background-color: {{ $buttonColor }};"
                    >
                        <svg x-show="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span :class="{'hidden': loading}">{{ $submitText }}</span>
                        <span x-show="loading" class="hidden">{{ __('Sending...') }}</span>
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

    @if($formBgColorDark || $buttonColorDark)
        <style>
            @if($formBgColorDark)
            .dark #{{ $uniqueId }} {
                background-color: {{ $formBgColorDark }} !important;
            }
            @endif
            @if($buttonColorDark)
            .dark #{{ $uniqueId }} button[type="submit"] {
                background-color: {{ $buttonColorDark }} !important;
            }
            @endif
        </style>
    @endif
</x-blogr::background-wrapper>
</div>
