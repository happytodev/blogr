@props(['data'])

@php
use Happytodev\Blogr\Helpers\LinkResolver;

$heading = $data['heading'] ?? '';
$subheading = $data['subheading'] ?? '';
$buttonText = $data['button_text'] ?? '';
$buttonUrl = LinkResolver::resolve($data, 'button_link_type', 'button_url', 'button_category_id', 'button_cms_page_id');
$buttonStyle = $data['button_style'] ?? 'primary';
@endphp

<x-blogr::background-wrapper :data="$data" class="py-16 sm:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">
            {{ $heading }}
        </h2>
        
        @if($subheading)
            <p class="subtitle text-xl mb-8">
                {{ $subheading }}
            </p>
        @endif
        
        @if($buttonText && $buttonUrl)
            <a href="{{ $buttonUrl }}" 
               class="inline-flex items-center px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-200
                      {{ $buttonStyle === 'primary' 
                          ? 'bg-white text-gray-900 hover:bg-gray-100' 
                          : 'bg-gray-900 text-white hover:bg-gray-800 border-2 border-white' 
                      }}">
                {{ $buttonText }}
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        @endif
    </div>
</x-blogr::background-wrapper>
