@props(['links' => null, 'size' => 'w-5 h-5'])

@php
$socialLinks = $links ?? config('blogr.ui.footer.social_links', []);
$showRss = config('blogr.rss.show_in_footer', false) && config('blogr.rss.enabled', true);
@endphp

@if(!empty($socialLinks) || $showRss)
<div {{ $attributes->merge(['class' => 'flex items-center space-x-4']) }}>
    @if($showRss)
    @php
        $feedUrl = config('blogr.locales.enabled', false)
            ? route('blog.feed', ['locale' => app()->getLocale()])
            : route('blog.feed');
    @endphp
    <a href="{{ $feedUrl }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="RSS Feed">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93v-2.83Z"/>
        </svg>
    </a>
    @endif

    @if($twitter = $socialLinks['twitter'] ?? null)
    <a href="{{ $twitter }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Twitter/X">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
        </svg>
    </a>
    @endif

    @if($github = $socialLinks['github'] ?? null)
    <a href="{{ $github }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="GitHub">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
        </svg>
    </a>
    @endif

    @if($linkedin = $socialLinks['linkedin'] ?? null)
    <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="LinkedIn">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
        </svg>
    </a>
    @endif

    @if($facebook = $socialLinks['facebook'] ?? null)
    <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Facebook">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
    </a>
    @endif

    @if($bluesky = $socialLinks['bluesky'] ?? null)
    <a href="{{ $bluesky }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Bluesky">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.038.415-.05-.138.012-.276.03-.415.05-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 01-.415-.05c.14.012.279.03.415.05 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/>
        </svg>
    </a>
    @endif

    @if($youtube = $socialLinks['youtube'] ?? null)
    <a href="{{ $youtube }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="YouTube">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
        </svg>
    </a>
    @endif

    @if($instagram = $socialLinks['instagram'] ?? null)
    <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Instagram">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
        </svg>
    </a>
    @endif

    @if($tiktok = $socialLinks['tiktok'] ?? null)
    <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="TikTok">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
        </svg>
    </a>
    @endif

    @if($mastodon = $socialLinks['mastodon'] ?? null)
    <a href="{{ $mastodon }}" target="_blank" rel="noopener noreferrer me"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Mastodon">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.61 7.837.661 9.143c.074 1.874.088 3.745.26 5.611.118 1.24.325 2.47.62 3.68.55 2.237 2.777 4.098 4.96 4.857 2.336.792 4.849.923 7.256.38.265-.061.527-.132.786-.213.585-.184 1.27-.39 1.774-.753a.057.057 0 0 0 .023-.043v-1.809a.052.052 0 0 0-.02-.041.053.053 0 0 0-.046-.01 20.282 20.282 0 0 1-4.709.545c-2.73 0-3.463-1.284-3.674-1.818a5.593 5.593 0 0 1-.319-1.433.053.053 0 0 1 .066-.054c1.517.363 3.072.546 4.632.546.376 0 .75 0 1.125-.01 1.57-.044 3.224-.124 4.768-.422.038-.008.077-.015.11-.024 2.435-.464 4.753-1.92 4.989-5.604.008-.145.03-1.52.03-1.67.002-.512.167-3.63-.024-5.545zm-3.748 9.195h-2.561V8.29c0-1.309-.55-1.976-1.67-1.976-1.23 0-1.846.79-1.846 2.35v3.403h-2.546V8.663c0-1.56-.617-2.35-1.848-2.35-1.112 0-1.668.668-1.67 1.977v6.218H4.822V8.102c0-1.31.337-2.35 1.011-3.12.696-.77 1.608-1.164 2.74-1.164 1.311 0 2.302.5 2.962 1.498l.638 1.06.638-1.06c.66-.999 1.65-1.498 2.96-1.498 1.13 0 2.043.395 2.74 1.164.675.77 1.012 1.81 1.012 3.12z"/>
        </svg>
    </a>
    @endif

    @if($discord = $socialLinks['discord'] ?? null)
    <a href="{{ $discord }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Discord">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.054C1.483 8.097.74 11.72.926 15.28a.07.07 0 0 0 .035.054c2.014 1.476 3.96 2.373 5.87 2.967.113.04.226.006.304-.08.324-.443.614-.91.863-1.404a.07.07 0 0 0-.038-.1 13.07 13.07 0 0 1-1.32-.629.07.07 0 0 1-.014-.115c.089-.067.178-.136.263-.206a.07.07 0 0 1 .07-.014c2.814 1.284 5.869 1.284 8.65 0a.07.07 0 0 1 .07.014c.086.07.175.14.264.206a.07.07 0 0 1-.013.115c-.425.247-.87.463-1.32.629a.07.07 0 0 0-.037.1c.25.494.54.961.863 1.404.078.087.191.12.304.08 1.908-.594 3.856-1.49 5.87-2.967a.07.07 0 0 0 .035-.054c.224-4.007-.569-7.662-2.343-10.859a.068.068 0 0 0-.032-.054zM8.52 12.66c-.85 0-1.541-.782-1.541-1.74 0-.957.676-1.74 1.541-1.74.866 0 1.557.783 1.541 1.74 0 .958-.675 1.74-1.541 1.74zm6.94 0c-.85 0-1.541-.782-1.541-1.74 0-.957.675-1.74 1.541-1.74.866 0 1.557.783 1.541 1.74 0 .958-.675 1.74-1.541 1.74z"/>
        </svg>
    </a>
    @endif

    @if($kofi = $socialLinks['kofi'] ?? null)
    <a href="{{ $kofi }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Ko-fi">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 504 504">
            <path d="M380.19,276.5A196.26,196.26,0,0,1,352,277.78V185.62h19.2a38.37,38.37,0,0,1,32,15.36,45.65,45.65,0,0,1,10.24,29.44A42.87,42.87,0,0,1,380.19,276.5Zm79.37-64a83.86,83.86,0,0,0-37.13-57.61A98.23,98.23,0,0,0,366.11,137H84.49a16.37,16.37,0,0,0-14.08,15.36v3.84s-1.28,124.17,1.28,192a42.11,42.11,0,0,0,42.24,39.68s129.29,0,190.73-1.28h9c35.84-9,38.4-42.24,38.4-60.16C422.43,329,472.36,279.06,459.56,212.5Z"/>
            <path d="M208.66,334.11c3.84,1.28,5.12,0,5.12,0s44.8-41,65.28-65.29c17.92-21.76,19.2-56.32-11.52-70.4s-56.32,15.36-56.32,15.36a50.44,50.44,0,0,0-70.41-7.68l-1.28,1.28c-15.36,16.64-10.24,44.8,1.28,60.16a771.87,771.87,0,0,0,65.29,64Z"/>
        </svg>
    </a>
    @endif

    @if($substack = $socialLinks['substack'] ?? null)
    <a href="{{ $substack }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="Newsletter (Substack)">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 24 24">
            <path d="M22.539 8.242H1.46V5.406h21.08v2.836zM1.46 10.812V24L12 18.11 22.54 24V10.812H1.46zM22.54 0H1.46v2.836h21.08V0z"/>
        </svg>
    </a>
    @endif

    @if($furaffinity = $socialLinks['furaffinity'] ?? null)
    <a href="{{ $furaffinity }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="FurAffinity">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 120 123">
            <path d="M40.06,0.37c9.4,0,17.03,11.69,17.03,26.1s-7.63,26.1-17.03,26.1c-9.4,0-17.03-11.68-17.03-26.1C23.04,12.06,30.66,0.37,40.06,0.37z M61.71,63.55c19.94,0.04,22.42,13.25,39.23,35.86c8.38,16.45-2.5,26.82-21.15,22.38c-8.46-4.31-14.41-5.83-20.38-5.63c-10.34,0.36-12.95,7.18-24.98,6.7c-9.28-0.25-13.46-4.14-14.27-10.07c-0.87-6.3,1.56-10.28,4.52-15.49C36.18,77.02,48.07,61.01,61.71,63.55z M7.17,39.08C0.14,41.86-2.1,52.85,2.16,63.62C6.42,74.39,15.57,80.87,22.6,78.09c7.03-2.78,9.27-13.77,5.01-24.54C23.35,42.78,14.2,36.3,7.17,39.08z M112.55,39.08c7.03,2.78,9.27,13.77,5.01,24.54c-4.26,10.77-13.42,17.25-20.44,14.47c-7.03-2.78-9.27-13.77-5.01-24.54C96.37,42.78,105.52,36.3,112.55,39.08z M79.35,0c9.4,0,17.03,11.69,17.03,26.1s-7.63,26.1-17.03,26.1c-9.4,0-17.03-11.68-17.03-26.1C62.33,11.69,69.95,0,79.35,0z"/>
        </svg>
    </a>
    @endif

    @if($vgen = $socialLinks['vgen'] ?? null)
    <a href="{{ $vgen }}" target="_blank" rel="noopener noreferrer"
       class="text-gray-600 dark:text-gray-400 hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors inline-flex items-center justify-center p-1 min-w-[24px] min-h-[24px]"
       title="VGen">
        <svg class="{{ $size }}" fill="currentColor" viewBox="0 0 48 38">
            <path fill-rule="evenodd" d="M20.725 10.108a8.205 8.205 0 0 1 11.589.607l.388.431.005.005c1.761 1.903 2.844 4.188 2.793 5.87v.005c-.01.41-.126.826-.358 1.203l-.028.045-.067.107-.249.386a50 50 0 0 1-4.099 5.427c-1.295 1.482-2.846 3.042-4.521 4.25-1.632 1.175-3.657 2.217-5.848 2.217-2.166 0-4.25-1.022-5.962-2.172-1.77-1.186-3.46-2.725-4.897-4.197a58 58 0 0 1-3.58-4.054 57 57 0 0 1-1.33-1.719l-.078-.105-.03-.042c-.213-.336-.35-.575-.423-1.036a2.39 2.39 0 0 1 .82-2.21c.646-.55 1.49-.65 2.05-.51.72.16 1.14.53 1.45.948l.001.001.001.002.015.02a18 18 0 0 0 .31.417c.222.292.547.714.956 1.222a53 53 0 0 0 3.275 3.71c1.32 1.35 2.749 2.634 4.136 3.564 1.442.968 2.55 1.357 3.286 1.357.544 0 1.262-.216 2.135-.721l.609-.352-.532-.46a5 5 0 0 1-.482-.472l-1.942-2.156a8.205 8.205 0 0 1 .607-11.588m9.107 7.416a45 45 0 0 1-2.75 3.51l-.025.028-.35.4-3.01-2.97-.01-.01a3.402 3.402 0 1 1 5.057-4.553l.388.431c.377.418.699.882.858 1.4a2.33 2.33 0 0 1-.11 1.678l-.02.046z"/>
        </svg>
    </a>
    @endif
</div>
@endif
