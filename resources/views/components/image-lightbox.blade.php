<div
    x-data="{
        open: false,
        src: '',
        caption: '',
        init() {
            var self = this;
            document.addEventListener('click', function (e) {
                var trigger = e.target.closest('.blogr-lightbox-trigger');
                if (trigger) {
                    e.preventDefault();
                    self.src = trigger.href;
                    self.caption = trigger.dataset.caption || '';
                    self.open = true;
                    document.body.style.overflow = 'hidden';
                }
            });
        },
        close() {
            this.open = false;
            document.body.style.overflow = '';
        }
    }"
    x-show="open"
    x-cloak
    @keydown.window.escape="close"
    @click.self="close"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
    role="dialog"
    aria-modal="true"
    :aria-label="caption || 'Image'"
>
    <figure class="relative max-w-[90vw] max-h-[90vh] flex flex-col items-center">
        <img :src="src" :alt="caption" class="max-w-full max-h-[85vh] w-auto h-auto object-contain rounded-lg shadow-2xl">
        <figcaption x-show="caption" x-text="caption" class="mt-3 text-sm text-white/80 text-center max-w-lg"></figcaption>
        <button @click="close" class="absolute -top-3 -right-3 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition-colors focus:outline-none focus:ring-2 focus:ring-white/50" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </figure>
</div>
