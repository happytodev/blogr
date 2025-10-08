@props(['items' => [], 'post' => null])

@php
    use Happytodev\Blogr\Models\BlogPost;
    
    $breadcrumbs = [];
    $currentLocale = app()->getLocale();
    
    // Home
    $breadcrumbs[] = [
        'label' => 'Home',
        'url' => url('/'),
    ];
    
    // Blog
    $breadcrumbs[] = [
        'label' => 'Blog',
        'url' => route('blog.index', ['locale' => $currentLocale]),
    ];
    
    // Add series if post belongs to one
    if ($post instanceof BlogPost && $post->blog_series_id) {
        $series = $post->series;
        $seriesTranslation = $series?->translate($currentLocale) ?? $series?->getDefaultTranslation();
        
        if ($series) {
            $breadcrumbs[] = [
                'label' => $seriesTranslation?->title ?? $series->slug,
                'url' => route('blog.series', ['locale' => $currentLocale, 'seriesSlug' => $series->slug]),
            ];
        }
    }
    
    // Add custom items
    foreach ($items as $item) {
        $breadcrumbs[] = $item;
    }
@endphp

@if(count($breadcrumbs) > 1)
<nav class="breadcrumb flex items-center space-x-2 text-sm text-gray-600 mb-6" aria-label="Breadcrumb" {{ $attributes }}>
    @foreach($breadcrumbs as $index => $breadcrumb)
        @if($index > 0)
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
        @endif
        
        @if(isset($breadcrumb['url']) && $index < count($breadcrumbs) - 1)
            <a href="{{ $breadcrumb['url'] }}" class="hover:text-blue-600 transition-colors">
                {{ $breadcrumb['label'] }}
            </a>
        @else
            <span class="text-gray-900 font-medium" aria-current="page">
                {{ $breadcrumb['label'] }}
            </span>
        @endif
    @endforeach
</nav>

{{-- Schema.org Breadcrumb structured data --}}
<script type="application/ld+json">
@php
    $jsonItems = [];
    foreach($breadcrumbs as $index => $breadcrumb) {
        $item = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $breadcrumb['label']
        ];
        if (isset($breadcrumb['url'])) {
            $item['item'] = $breadcrumb['url'];
        }
        $jsonItems[] = $item;
    }
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $jsonItems
    ];
@endphp
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
