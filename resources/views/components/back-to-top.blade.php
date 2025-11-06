@php
    $enabled = config('blogr.ui.back_to_top.enabled', true);
    $shape = config('blogr.ui.back_to_top.shape', 'circle');
    $color = config('blogr.ui.back_to_top.color', null);
    
    // Determine button classes based on shape
    $roundedClass = $shape === 'circle' ? 'rounded-full' : 'rounded-lg';
    
    // Determine background color style
    $bgStyle = $color ? "background: {$color};" : "background: var(--color-primary);";
@endphp

@if($enabled)
<button id="blogr-back-to-top" aria-label="Back to top"
    class="fixed z-50 bottom-6 right-6 p-3 shadow-lg transition-opacity duration-200 opacity-90 hover:opacity-100 {{ $roundedClass }}"
    style="{{ $bgStyle }} color: white;">
    <!-- simple chevron up icon -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
    </svg>
</button>

<script>
    (function () {
        const btn = document.getElementById('blogr-back-to-top');
        if (!btn) return;

        // hide on very small screens if disabled by config - handled by CSS classes if needed
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
        });

        // optional: hide when at top
        const onScroll = () => {
            if (window.scrollY > 200) {
                btn.style.display = 'block';
            } else {
                btn.style.display = 'none';
            }
        };

        // init
        onScroll();
        window.addEventListener('scroll', onScroll);
    })();
</script>
@endif
