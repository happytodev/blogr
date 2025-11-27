@props(['data'])

@php
    use Happytodev\Blogr\Models\BlogPost;
    
    $heading = $data['heading'] ?? __('Latest Posts');
    $limit = $data['limit'] ?? 3;
    $layout = $data['layout'] ?? 'grid';
    
    $posts = BlogPost::with(['translations' => function($query) {
        $query->where('locale', app()->getLocale());
    }])
    ->where('is_published', true)
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->take($limit)
    ->get();
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading)
            <h2 class="text-3xl sm:text-4xl font-bold mb-12 text-center">
                {{ $heading }}
            </h2>
        @endif

        @if($posts->count() > 0)
            <div class="{{ $layout === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8' : 'space-y-8 max-w-3xl mx-auto' }}">
                @foreach($posts as $post)
                    @php
                        $translation = $post->translations->first();
                    @endphp
                    
                    @if($translation)
                        <article class="group">
                            @if($translation->featured_image)
                                <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'slug' => $translation->slug]) }}" class="block mb-4 overflow-hidden rounded-lg">
                                    <img 
                                        src="{{ Storage::url($translation->featured_image) }}" 
                                        alt="{{ $translation->title }}"
                                        class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
                                    >
                                </a>
                            @endif
                            
                            <div class="subtitle text-sm mb-2">
                                {{ $post->published_at->format('M d, Y') }}
                            </div>
                            
                            <h3 class="text-xl font-bold mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'slug' => $translation->slug]) }}">
                                    {{ $translation->title }}
                                </a>
                            </h3>
                            
                            @if($translation->excerpt)
                                <p class="mb-4">
                                    {{ Str::limit($translation->excerpt, 120) }}
                                </p>
                            @endif
                            
                            <a href="{{ route('blog.show', ['locale' => app()->getLocale(), 'slug' => $translation->slug]) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                                {{ __('Read more') }} →
                            </a>
                        </article>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-center">
                {{ __('No blog posts available') }}
            </p>
        @endif
    </div>
</x-blogr::background-wrapper>
