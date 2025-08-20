<!DOCTYPE html>
<html>
<head>
    <title>Posts with tag {{ $tag->name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Posts with le tag {{ $tag->name }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $post)
                <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                    <!-- Always include a fixed-height image container -->
                    <div class="relative h-48 bg-gray-200">
                        <a href="{{ route('blog.show', $post->slug) }}">
                            @if ($post->photo)
                                <img src="{{ $post->photo_url }}" alt="{{ $post->title }}"
                                    class="absolute inset-0 w-full h-full object-cover rounded-t-lg">
                            @else
                                <!-- Optional: Placeholder text or icon for no image -->
                                <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                                    No image available
                                </div>
                            @endif
                        </a>
                    </div>
                    <div
                        class="p-4 {{ config('blogr.blog_index.cards.colors.background', 'bg-white') }} flex-grow border-t-4 {{ config('blogr.blog_index.cards.colors.top_border', 'bg-white') }}">
                        <h2 class="text-xl font-semibold mb-2">{{ $post->title }}</h2>
                        <div class="mb-2">
                            <span class="text-sm text-gray-600">
                                Catégory:
                                <a href="{{ route('blog.category', $post->category->slug) }}"
                                    class="text-blue-500 hover:underline">
                                    {{ $post->category->name }}
                                </a>
                            </span>
                        </div>
                        @if ($post->tags->count())
                            <div class="mb-2">
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
                            <div class="text-gray-700 mb-4">
                                <p class="font-bold mb-2">TL;DR : </p>
                                <p class="italic mb-4">{{ $post->tldr }}</p>
                            </div>
                        @endif
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-500 hover:underline">Read
                            more</a>
                    </div>
                </div>
            @endforeach
        </div>
        <a href="{{ route('blog.index') }}" class="text-blue-500 hover:underline mt-4 inline-block">Back to blog</a>
    </div>
</body>
</html>