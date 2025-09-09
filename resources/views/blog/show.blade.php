@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = $seoData ?? [];
    @endphp
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-5xl font-bold mb-4">{{ $post->title }}</h1>
        @if ($post->photo)
            <img src="{{ $post->photo_url }}" alt="{{ $post->title }}" class="w-3/5 mx-auto h-auto my-12 rounded-lg">
        @endif
        <div class="mb-4">
            <span class="text-sm text-gray-600">
                Category:
                <a href="{{ route('blog.category', $post->category->slug) }}" class="text-blue-500 hover:underline">
                    {{ $post->category->name }}
                </a>
            </span>
            @if(config('blogr.reading_time.enabled', true))
            <span class="text-sm text-gray-600 ml-4">
                @include('blogr::components.clock-icon')
                {{ $post->reading_time }}
            </span>
            @endif
        </div>
        @if ($post->tags->count())
            <div class="mb-4">
                <span class="text-sm text-gray-600">Tags: </span>
                @foreach ($post->tags as $tag)
                    <a href="{{ route('blog.tag', $tag->slug) }}"
                        class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
        @if ($post->tldr)
            <div class="text-gray-700 mb-4 border-2 border-gray-300 p-4 rounded-lg">
                <p class="font-bold mb-2">TL;DR : </p>
                <p class="italic mb-4">{{ $post->tldr }}</p>
            </div>
        @endif
        <div class="prose max-w-none">{!! $post->getContentWithoutFrontmatter() !!}</div>
        <a href="{{ route('blog.index') }}" class="text-blue-500 hover:underline mt-4 inline-block">Back to blog</a>
    </div>
@endsection
