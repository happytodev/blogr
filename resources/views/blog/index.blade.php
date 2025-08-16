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
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    @if ($post->photo)
                        <img src="{{ $post->photo_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover rounded-t-lg">
                    @endif
                    <div class="p-4 bg-green-50">
                        <h2 class="text-xl font-semibold mb-2">{{ $post->title }}</h2>
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-500 hover:underline">Read more</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>