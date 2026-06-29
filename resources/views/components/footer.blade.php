@if(config('blogr.ui.footer.enabled', true))
<footer style="background-color: var(--color-footer-bg);" class="border-t border-gray-200 dark:border-gray-700 mt-auto transition-colors duration-200">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <!-- Footer Text -->
            <div class="text-center md:text-left text-gray-600 dark:text-gray-400 text-sm">
                {!! config('blogr.ui.footer.text', '© ' . date('Y') . ' My Blog. All rights reserved.') !!}
            </div>

            <!-- Social Links -->
            @stack('footer-links')
            @if(config('blogr.ui.footer.show_social_links', true))
                <x-blogr::social-links />
            @endif
        </div>

        <!-- Additional Links (optional) -->
        <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-500">
            <span>Powered by <a href="https://github.com/happytodev/blogr" class="hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)] transition-colors">Blogr</a></span>
        </div>
    </div>
</footer>
@endif
