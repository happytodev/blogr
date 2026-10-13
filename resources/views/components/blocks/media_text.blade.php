@props(['data'])

{{-- An image beside rich text, alternating sides down a page. Issue #353. --}}

@php
    use Happytodev\Blogr\Helpers\MarkdownHelper;

    $eyebrow = $data['eyebrow'] ?? null;
    $title = $data['title'] ?? null;
    $subtitle = $data['subtitle'] ?? null;
    $content = $data['content'] ?? null;
    $items = $data['items'] ?? [];
    $image = $data['image'] ?? null;
    $imageCaption = $data['image_caption'] ?? null;

    // The content editor stores lists keyed by UUID once a block is edited. We
    // always work from a list, never from the key: a key is not a position.
    // Same family as the carousel fix (#223) — see also #350 and #351.
    if (is_array($items)) {
        $items = array_values(array_filter($items, fn ($item) => is_array($item)));
    }

    // The image may arrive as a UUID-keyed array from a Repeater upload too.
    if (is_array($image)) {
        $image = reset($image) ?: null;
    }

    $side = ($data['side'] ?? 'right') === 'left' ? 'left' : 'right';

    $imageAlt = $data['image_alt'] ?? ($title ? strip_tags($title) : '');

    // Escape first, emphasise after: *word* becomes <em>, and nothing else in the
    // title can leak markup.
    $titleHtml = $title ? preg_replace('/\*(.+?)\*/s', '<em>$1</em>', e($title)) : null;
@endphp

@if($title || $content || $image || count($items))
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">

            {{-- Text column: left by default, right when the image takes the left. --}}
            <div class="{{ $side === 'right' ? 'lg:order-1' : 'lg:order-2' }}">
                @if($eyebrow)
                    <p class="mb-3 font-mono text-xs font-medium uppercase tracking-widest text-[var(--color-primary)]">
                        {{ $eyebrow }}
                    </p>
                @endif

                @if($titleHtml)
                    <h2 class="text-3xl font-bold leading-tight sm:text-4xl">{!! $titleHtml !!}</h2>
                @endif

                @if($subtitle)
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">{{ $subtitle }}</p>
                @endif

                @if($content)
                    <div class="prose prose-lg mt-6 max-w-none dark:prose-invert prose-a:text-[var(--color-primary)]">
                        {!! MarkdownHelper::toHtml($content) !!}
                    </div>
                @endif

                @if(count($items))
                    <ul class="mt-8 space-y-5">
                        @foreach($items as $item)
                            <li>
                                @if(!empty($item['title']))
                                    <p class="font-semibold">{{ $item['title'] }}</p>
                                @endif
                                @if(!empty($item['text']))
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">{{ $item['text'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Image column. `object-contain` with a natural width: the source keeps
                 its own ratio, and a wide screenshot is not cropped. --}}
            @if($image)
                <figure class="{{ $side === 'right' ? 'lg:order-2' : 'lg:order-1' }}">
                    <img
                        src="{{ Storage::url($image) }}"
                        alt="{{ $imageAlt }}"
                        loading="lazy"
                        class="h-auto w-full rounded-xl border border-gray-200 object-contain dark:border-gray-700"
                    >
                    @if($imageCaption)
                        <figcaption class="mt-3 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ $imageCaption }}
                        </figcaption>
                    @endif
                </figure>
            @endif
        </div>
    </div>
@endif
