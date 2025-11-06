@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = [
            'title' => $seoTitle ?? $title,
            'description' => $seoDescription ?? '',
            'keywords' => $seoKeywords ?? '',
        ];
    @endphp
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800">
    <!-- Hero Section -->
    <div class="relative isolate px-6 pt-14 lg:px-8">
        <div class="mx-auto max-w-4xl py-32 sm:py-48 lg:py-56">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                    {{ $title }}
                </h1>
                
                @if(isset($translation) && $translation->excerpt)
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        {{ $translation->excerpt }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="prose prose-lg dark:prose-invert max-w-none">
            {!! \Illuminate\Support\Str::markdown($content) !!}
        </div>
    </div>

    <!-- Blocks Section -->
    @if(isset($blocks) && !empty($blocks))
        <x-blogr::blocks-renderer :blocks="$blocks" />
    @endif
</div>
@endsection
