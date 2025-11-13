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
<div class="bg-white dark:bg-gray-900 min-h-screen">
    <!-- Page Header -->
    <div class="bg-gradient-to-b from-purple-50 to-white dark:from-gray-800 dark:to-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                    {{ $title }}
                </h1>
                
                @if(isset($translation) && $translation->excerpt)
                    <p class="mt-4 text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        {{ $translation->excerpt }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Content Section -->
    @if($content)
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="prose prose-lg dark:prose-invert max-w-none">
                {!! \Illuminate\Support\Str::markdown($content) !!}
            </div>
        </div>
    @endif

    <!-- Blocks Section (Pricing tables, features, etc.) -->
    @if(isset($blocks) && !empty($blocks))
        <x-blogr::blocks-renderer :blocks="$blocks" />
    @endif
</div>
@endsection
