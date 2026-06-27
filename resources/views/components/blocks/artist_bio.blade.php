@php
$avatar = $data['avatar'] ?? null;
if (is_array($avatar)) {
    $avatar = is_array($avatar) ? (reset($avatar) ?: null) : $avatar;
}
$title = $data['title'] ?? null;
$bio = $data['bio'] ?? null;
$layout = $data['layout'] ?? 'left';
$socialLinks = $data['social_links'] ?? [];
@endphp

@if($title || $bio || $avatar)
<x-blogr::background-wrapper :data="$data">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col {{ $layout === 'left' ? 'md:flex-row' : '' }} items-center {{ $layout === 'left' ? 'md:items-start' : 'text-center' }} gap-8">
            @if($avatar)
            <div class="flex-shrink-0">
                <img
                    src="{{ Storage::url($avatar) }}"
                    alt="{{ $title ?? '' }}"
                    class="w-40 h-40 rounded-full object-cover shadow-lg {{ $layout === 'center' ? 'mx-auto' : '' }}"
                    loading="lazy"
                >
            </div>
            @endif

            <div class="flex-1 {{ $layout === 'center' ? 'text-center' : '' }}">
                @if($title)
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    {{ $title }}
                </h2>
                @endif

                @if($bio)
                <div class="prose prose-lg dark:prose-invert max-w-none mb-6">
                    {{ $bio }}
                </div>
                @endif

                @if(count($socialLinks) > 0)
                    @php
                        $linkMap = [];
                        foreach ($socialLinks as $link) {
                            if (!empty($link['platform']) && !empty($link['url'])) {
                                $linkMap[$link['platform']] = $link['url'];
                            }
                        }
                    @endphp
                    <x-blogr::social-links :links="$linkMap" size="w-6 h-6" />
                @endif
            </div>
        </div>
    </div>
</x-blogr::background-wrapper>
@endif
