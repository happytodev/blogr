@php
$avatar = $data['avatar'] ?? null;
if (is_array($avatar)) {
    $avatar = reset($avatar) ?: null;
}
$title = $data['title'] ?? null;
$bio = $data['bio'] ?? null;
$layout = $data['layout'] ?? 'left';
$imageWidth = $data['image_width'] ?? '5/12';
$imageStyle = $data['image_style'] ?? 'circle';
$socialLinks = $data['social_links'] ?? [];

$imagePct = match ($imageWidth) {
    '1/4' => '25',
    '1/3' => '33.33',
    '5/12' => '41.67',
    '1/2' => '50',
    '7/12' => '58.33',
    '2/3' => '66.67',
    '3/4' => '75',
    default => '41.67',
};

$textPct = 100 - (float) $imagePct;

$uid = 'bio-' . md5($title . $layout . $imageWidth . $imageStyle);

$imageStyleClasses = match ($imageStyle) {
    'circle' => 'aspect-square rounded-full object-cover',
    'square' => 'aspect-square object-cover',
    'rounded_square' => 'aspect-square rounded-2xl object-cover',
    'rectangle' => 'aspect-[4/3] object-cover',
    'rounded_rectangle' => 'aspect-[4/3] rounded-2xl object-cover',
    'full' => 'w-full object-cover h-auto',
    default => 'aspect-square rounded-full object-cover',
};
@endphp

@if($title || $bio || $avatar)
@if($layout !== 'center')
<style>
#{{ $uid }} { display: flex; flex-direction: column; gap: 2rem; }
#{{ $uid }} > .bio-img { width: 100%; }
#{{ $uid }} > .bio-text { width: 100%; }
@media (min-width: 768px) {
    #{{ $uid }} { flex-direction: {{ $layout === 'right' ? 'row-reverse' : 'row' }}; align-items: flex-start; }
    #{{ $uid }} > .bio-img { flex: 0 0 {{ $imagePct }}%; max-width: {{ $imagePct }}%; }
    #{{ $uid }} > .bio-text { flex: 1 1 {{ $textPct }}%; }
}
</style>
@endif

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($layout === 'center')
        <div class="flex flex-col items-center text-center gap-8">
            @if($avatar)
            <div class="w-full max-w-md mx-auto">
                <img src="{{ Storage::url($avatar) }}" alt="{{ $title ?? '' }}" class="w-full {{ $imageStyleClasses }} shadow-lg mx-auto" loading="lazy">
            </div>
            @endif
            <div class="w-full max-w-3xl text-center">
                @if($title)<h2 class="text-3xl sm:text-4xl font-bold mb-4">{{ $title }}</h2>@endif
                @if($bio)<div class="prose prose-lg dark:prose-invert max-w-none mb-6">{!! \Happytodev\Blogr\Helpers\MarkdownHelper::toHtml($bio) !!}</div>@endif
                @if(count($socialLinks) > 0)
                    @php $linkMap = []; foreach ($socialLinks as $link) { if (!empty($link['platform']) && !empty($link['url'])) { $linkMap[$link['platform']] = $link['url']; } } @endphp
                    <x-blogr::social-links :links="$linkMap" size="w-6 h-6" />
                @endif
            </div>
        </div>
        @else
        <div id="{{ $uid }}">
            @if($avatar)
            <div class="bio-img">
                <img src="{{ Storage::url($avatar) }}" alt="{{ $title ?? '' }}" class="w-full {{ $imageStyleClasses }} shadow-lg" loading="lazy">
            </div>
            @endif
            <div class="bio-text">
                @if($title)<h2 class="text-3xl sm:text-4xl font-bold mb-4">{{ $title }}</h2>@endif
                @if($bio)<div class="prose prose-lg dark:prose-invert max-w-none mb-6">{!! \Happytodev\Blogr\Helpers\MarkdownHelper::toHtml($bio) !!}</div>@endif
                @if(count($socialLinks) > 0)
                    @php $linkMap = []; foreach ($socialLinks as $link) { if (!empty($link['platform']) && !empty($link['url'])) { $linkMap[$link['platform']] = $link['url']; } } @endphp
                    <x-blogr::social-links :links="$linkMap" size="w-6 h-6" />
                @endif
            </div>
        </div>
        @endif
    </div>
</x-blogr::background-wrapper>
@endif
