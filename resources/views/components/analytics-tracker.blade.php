{{-- 
    Analytics Tracker Component
    
    Injects the appropriate analytics tracking script based on configuration.
    Supports: Google Analytics, Plausible, Umami, Matomo
--}}

@php
    $analyticsEnabled = config('blogr.analytics.enabled', false);
    $provider = config('blogr.analytics.provider');
@endphp

@if($analyticsEnabled && $provider)
    {{-- Google Analytics --}}
    @if($provider === 'google')
        @php
            $measurementId = config('blogr.analytics.google.measurement_id');
        @endphp
        @if($measurementId)
            <!-- Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ $measurementId }}');
            </script>
        @endif
    @endif

    {{-- Plausible Analytics --}}
    @if($provider === 'plausible')
        @php
            $domain = config('blogr.analytics.plausible.domain');
            $src = config('blogr.analytics.plausible.src') ?: 'https://plausible.io/js/script.js';
        @endphp
        @if($domain)
            <!-- Plausible Analytics -->
            <script defer data-domain="{{ $domain }}" src="{{ $src }}"></script>
        @endif
    @endif

    {{-- Umami Analytics --}}
    @if($provider === 'umami')
        @php
            $websiteId = config('blogr.analytics.umami.website_id');
            $src = config('blogr.analytics.umami.src');
        @endphp
        @if($websiteId && $src)
            <!-- Umami Analytics -->
            <script defer src="{{ $src }}" data-website-id="{{ $websiteId }}"></script>
        @endif
    @endif

    {{-- Matomo Analytics --}}
    @if($provider === 'matomo')
        @php
            $matomoUrl = config('blogr.analytics.matomo.url');
            $siteId = config('blogr.analytics.matomo.site_id');
        @endphp
        @if($matomoUrl && $siteId)
            <!-- Matomo Analytics -->
            <script>
                var _paq = window._paq = window._paq || [];
                _paq.push(['trackPageView']);
                _paq.push(['enableLinkTracking']);
                (function() {
                    var u="{{ rtrim($matomoUrl, '/') }}/";
                    _paq.push(['setTrackerUrl', u+'matomo.php']);
                    _paq.push(['setSiteId', '{{ $siteId }}']);
                    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
                })();
            </script>
            <noscript>
                <p><img referrerpolicy="no-referrer-when-downgrade" src="{{ rtrim($matomoUrl, '/') }}/matomo.php?idsite={{ $siteId }}&amp;rec=1" style="border:0;" alt="" /></p>
            </noscript>
        @endif
    @endif
@endif
