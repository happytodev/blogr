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
    <!-- Blocks Section (Pricing tables, features, etc.) -->
    @if(isset($blocks) && !empty($blocks))
        <x-blogr::blocks-renderer :blocks="$blocks" />
    @endif
</div>
@endsection
