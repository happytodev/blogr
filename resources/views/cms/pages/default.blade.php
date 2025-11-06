@extends('blogr::layouts.blog')

@section('seo-data')
    @php
        $seoData = [
            'title' => $seoTitle,
            'description' => $seoDescription,
            'keywords' => $seoKeywords,
        ];
    @endphp
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <article class="prose prose-lg dark:prose-invert max-w-none">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                {{ $title }}
            </h1>
            
            @if($translation->excerpt)
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    {{ $translation->excerpt }}
                </p>
            @endif
        </header>

        <div class="cms-content">
            {!! \Illuminate\Support\Str::markdown($content) !!}
        </div>

        @if($translation->seo_keywords)
            <footer class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap gap-2">
                    @foreach(explode(',', $translation->seo_keywords) as $keyword)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ trim($keyword) }}
                        </span>
                    @endforeach
                </div>
            </footer>
        @endif
    </article>
</div>
@endsection
