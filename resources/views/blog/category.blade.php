@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@section('content')
    <div class="container mx-auto px-4 py-12">
        <!-- Page Header -->
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-bold mb-4 text-gray-900 dark:text-white">{{ __('blogr::blogr.ui.posts_in_category') }} {{ $displayName ?? $category->name }}</h1>
            <a href="{{ route('blog.index', $currentLocale) }}" 
               class="inline-flex items-center text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] font-semibold">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to blog
            </a>
        </div>

        <!-- Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 auto-rows-fr">
            @foreach ($posts as $post)
                <x-blogr::blog-post-card :post="$post" :currentLocale="$currentLocale" />
            @endforeach
        </div>

        <!-- Pagination Links -->
        @if($posts->hasPages())
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
