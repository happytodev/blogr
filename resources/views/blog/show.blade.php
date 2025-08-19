<!DOCTYPE html>
<html>

<head>
    <title>{{ $post->title }}</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-5xl font-bold mb-4">{{ $post->title }}</h1>
        @if ($post->photo)
            <img src="{{ $post->photo_url }}" alt="{{ $post->title }}" class="w-3/5 mx-auto h-auto my-12 rounded-lg">
        @endif
        @if ($post->tldr)
            <p class="text-gray-700 mb-4 prose">
                <span class="font-bold">TL;DR : </span>
                <span class="italic">{{ $post->tldr }}</span>
            </p>
        @endif
        <div class="prose max-w-none">{!! $post->content !!}</div>
        <a href="{{ route('blog.index') }}" class="text-blue-500 hover:underline mt-4 inline-block">Back to blog</a>
    </div>
</body>

</html>
