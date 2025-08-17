<!DOCTYPE html>
<html>

<head>
    <title>Blog</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Blog Posts</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $post)
                <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                    <!-- Always include a fixed-height image container -->
                    <div class="relative h-48 bg-gray-200">
                        @if ($post->photo)
                            <img src="{{ $post->photo_url }}" alt="{{ $post->title }}"
                                class="absolute inset-0 w-full h-full object-cover rounded-t-lg">
                        @else
                            <!-- Optional: Placeholder text or icon for no image -->
                            <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                                No image available
                            </div>
                        @endif
                    </div>
                    <div class="p-4 {{ config('blogr.blog_index.cards.colors.background', 'bg-white') }} flex-grow border-t-4 {{ config('blogr.blog_index.cards.colors.top_border', 'bg-white') }}">
                        <h2 class="text-xl font-semibold mb-2">{{ $post->title }}</h2>
                        @if ($post->tldr)
                            <p class="text-gray-700 mb-4">
                                <span class="font-bold">TL;DR : </span>
                                <span class="italic">{{ $post->tldr }}</span>
                            </p>
                        @endif
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-500 hover:underline">Read
                            more</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>