<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @yield('seo-data')

    <!-- Primary Meta Tags -->
    <title>{{ $seoData['title'] ?? config('blogr.seo.default_title') }}</title>
    <meta name="title" content="{{ $seoData['title'] ?? config('blogr.seo.default_title') }}">
    <meta name="description" content="{{ $seoData['description'] ?? config('blogr.seo.default_description') }}">
    <meta name="keywords" content="{{ $seoData['keywords'] ?? config('blogr.seo.default_keywords') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoData['canonical'] ?? url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoData['og_type'] ?? config('blogr.seo.og.type') }}">
    <meta property="og:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="og:title" content="{{ $seoData['title'] ?? config('blogr.seo.default_title') }}">
    <meta property="og:description" content="{{ $seoData['description'] ?? config('blogr.seo.default_description') }}">
    <meta property="og:image" content="{{ $seoData['image'] ?? asset(config('blogr.seo.og.image')) }}">
    <meta property="og:image:width" content="{{ $seoData['image_width'] ?? config('blogr.seo.og.image_width') }}">
    <meta property="og:image:height" content="{{ $seoData['image_height'] ?? config('blogr.seo.og.image_height') }}">
    <meta property="og:site_name" content="{{ config('blogr.seo.site_name') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seoData['canonical'] ?? url()->current() }}">
    <meta property="twitter:title" content="{{ $seoData['title'] ?? config('blogr.seo.default_title') }}">
    <meta property="twitter:description"
        content="{{ $seoData['description'] ?? config('blogr.seo.default_description') }}">
    <meta property="twitter:image" content="{{ $seoData['image'] ?? asset(config('blogr.seo.og.image')) }}">
    @if (config('blogr.seo.twitter_handle'))
        <meta property="twitter:site" content="{{ config('blogr.seo.twitter_handle') }}">
        <meta property="twitter:creator" content="{{ config('blogr.seo.twitter_handle') }}">
    @endif

    <!-- Additional Meta Tags -->
    <meta name="robots" content="{{ $seoData['robots'] ?? 'index, follow' }}">
    <meta name="author" content="{{ $seoData['author'] ?? config('blogr.seo.site_name') }}">

    @if (isset($seoData['published_time']))
        <meta property="article:published_time" content="{{ $seoData['published_time'] }}">
    @endif

    @if (isset($seoData['modified_time']))
        <meta property="article:modified_time" content="{{ $seoData['modified_time'] }}">
    @endif

    @if (isset($seoData['tags']) && is_array($seoData['tags']))
        @foreach ($seoData['tags'] as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Styles -->
    @vite(['resources/css/app.css'])

    <!-- Structured Data (JSON-LD) -->
    @if (config('blogr.seo.structured_data.enabled'))
        <script type="application/ld+json">
            {!! \Happytodev\Blogr\Helpers\SEOHelper::generateJsonLd($seoData) !!}
        </script>
    @endif

    @stack('head')
</head>

<body>
    @stack('body-start')

    <div class="min-h-screen bg-gray-50">
        @yield('content')
    </div>

    @stack('body-end')
</body>

</html>
