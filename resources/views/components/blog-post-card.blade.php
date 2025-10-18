@props(['post', 'currentLocale' => null])

@php
    $currentLocale = $currentLocale ?? app()->getLocale();
    $postTranslation = $post->translate($currentLocale);
    $postSlug = $postTranslation ? $postTranslation->slug : $post->slug;
    $postTitle = $postTranslation ? $postTranslation->title : $post->title;
    $postExcerpt = $postTranslation ? $postTranslation->excerpt : $post->excerpt;
    $postTldr = $postTranslation ? $postTranslation->tldr : $post->tldr;
@endphp

<article class="group bg-[var(--color-blog-card-bg)] dark:bg-[var(--color-blog-card-bg-dark)] rounded-xl shadow-lg hover:shadow-2xl overflow-hidden transition-all duration-300 transform hover:-translate-y-1 flex flex-col h-full">
    <!-- Post Image -->
    <div class="relative h-56 bg-gradient-to-br from-[var(--color-primary)] to-purple-600 overflow-hidden">
        <a href="{{ route('blog.show', ['locale' => $currentLocale, 'slug' => $postSlug]) }}"
            class="block h-full">
            @if ($post->photo)
                <img src="{{ $post->photo_url }}" alt="{{ $postTitle }}"
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
            @else
                <img src="{{ asset(config('blogr.posts.default_image', '/vendor/blogr/images/default-post.svg')) }}"
                    alt="{{ $postTitle }}"
                    class="absolute inset-0 w-full h-full object-cover opacity-50">
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            @endif
        </a>

        <!-- Category Badge -->
        <div class="absolute bottom-4 left-4">
            @php
                $categoryTranslation = $post->category->translate($currentLocale);
                $categoryName = $categoryTranslation ? $categoryTranslation->name : $post->category->name;
                $categorySlug = $categoryTranslation ? $categoryTranslation->slug : $post->category->slug;
            @endphp
            <a href="{{ config('blogr.locales.enabled') ? route('blog.category', ['locale' => $currentLocale, 'categorySlug' => $categorySlug]) : route('blog.category', ['categorySlug' => $categorySlug]) }}"
                class="inline-block bg-[var(--color-category-bg)] dark:bg-[var(--color-category-bg-dark)] backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold text-gray-900 dark:text-white hover:opacity-90 transition-colors">
                {{ $categoryName }}
            </a>
        </div>

        <!-- Reading Time Badge -->
        @if (config('blogr.reading_time.enabled', true))
            <div class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium text-white flex items-center">
                @include('blogr::components.clock-icon')
                <span class="ml-1">{{ $post->getFormattedReadingTime() }}</span>
            </div>
        @endif
    </div>

    <!-- Post Content -->
    <div class="p-6 flex-grow flex flex-col relative z-10">
        <!-- Title -->
        <h2 class="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-[var(--color-primary-hover)] dark:group-hover:text-[var(--color-primary-hover-dark)] transition-colors line-clamp-2">
            <a href="{{ config('blogr.locales.enabled') ? route('blog.show', ['locale' => $currentLocale, 'slug' => $postSlug]) : route('blog.show', ['slug' => $postSlug]) }}">
                {{ $postTitle }}
            </a>
        </h2>

        <!-- Excerpt or TL;DR -->
        @if ($postExcerpt)
            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                {{ $postExcerpt }}
            </p>
        @elseif ($postTldr)
            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3 italic">
                <span class="font-semibold">TL;DR:</span> {{ $postTldr }}
            </p>
        @endif

        <!-- Bottom Section: Tags + Author + Read More (always at bottom) -->
        <div class="mt-auto space-y-4">
            <!-- Tags -->
            @if ($post->tags->count())
                <div class="flex flex-wrap gap-2">
                    @foreach ($post->tags->take(3) as $tag)
                        @php
                            $tagTranslation = $tag->translate($currentLocale);
                            $tagName = $tagTranslation ? $tagTranslation->name : $tag->name;
                            $tagSlug = $tagTranslation ? $tagTranslation->slug : $tag->slug;
                        @endphp
                        <a href="{{ config('blogr.locales.enabled') ? route('blog.tag', ['locale' => $currentLocale, 'tagSlug' => $tagSlug]) : route('blog.tag', ['tagSlug' => $tagSlug]) }}"
                            class="inline-block bg-[var(--color-tag-bg)] dark:bg-[var(--color-tag-bg-dark)] text-gray-900 dark:text-white text-xs px-2.5 py-1 rounded-full hover:opacity-90 transition-colors">
                            #{{ $tagName }}
                        </a>
                    @endforeach
                    @if ($post->tags->count() > 3)
                        <span class="inline-block text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] text-xs px-2.5 py-1">
                            +{{ $post->tags->count() - 3 }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Author + Read More -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <!-- Author Info -->
                @if (config('blogr.display.show_author_pseudo') || config('blogr.display.show_author_avatar'))
                    <div class="flex-shrink-0">
                        <x-blogr::author-info :author="$post->user" size="sm" />
                    </div>
                @endif

                <!-- Read More Button -->
                <a href="{{ config('blogr.locales.enabled') ? route('blog.show', ['locale' => $currentLocale, 'slug' => $postSlug]) : route('blog.show', ['slug' => $postSlug]) }}"
                    class="inline-flex items-center text-[var(--color-primary)] dark:text-[var(--color-primary-dark)] hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] font-semibold text-sm group/link ml-auto">
                    {{ __('blogr::blogr.ui.read_more') }}
                    <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</article>
