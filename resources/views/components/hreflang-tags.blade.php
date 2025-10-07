@props(['currentRoute', 'routeParameters' => []])

@php
    use Happytodev\Blogr\Helpers\LocaleHelper;
    
    if (!config('blogr.locales.enabled', false)) {
        return;
    }
    
    $alternateUrls = LocaleHelper::alternateUrls($currentRoute, $routeParameters);
    $defaultLocale = config('blogr.locales.default', 'en');
@endphp

@if(count($alternateUrls) > 0)
    @foreach($alternateUrls as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternateUrls[$defaultLocale] ?? reset($alternateUrls) }}" />
@endif
