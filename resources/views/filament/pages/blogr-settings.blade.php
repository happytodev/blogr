<x-filament-panels::page>
    <div class="mb-4">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
            <input
                type="search"
                id="blogr-settings-search"
                placeholder="{{ __('blogr::blogr.settings.search_placeholder') }}"
                class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-primary-400"
            />
        </div>
    </div>

    <div id="blogr-no-results" class="mb-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 text-sm text-yellow-800 dark:text-yellow-200" style="display: none;">
        {{ __('blogr::blogr.settings.no_search_results') }}"<span id="blogr-search-term"></span>"
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6">
            {{ $this->form }}
        </div>

        <div class="flex justify-end !pt-16 !mt-16 border-t-2 border-gray-200 dark:border-gray-700" style="margin-top: 16px;">
            <x-filament::button
                type="submit"
                color="primary"
            >
                {{ __('blogr::blogr.settings.save') }}
            </x-filament::button>
        </div>
    </form>

    @php
        $presetsJson = json_encode(\Happytodev\Blogr\Filament\Pages\BlogrSettings::THEME_PRESETS);
    @endphp

    <style>
        .settings-search-badge {
            display: inline-block;
            margin-left: 6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 9999px;
            background: #4f46e5;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            line-height: 18px;
            text-align: center;
            box-sizing: border-box;
            vertical-align: middle;
        }
        .settings-tab-no-match {
            opacity: 0.35;
        }
        .settings-highlight {
            background: #fef08a;
            border-radius: 2px;
            padding: 0 1px;
        }
        .dark .settings-highlight {
            background: #854d0e;
            color: #fef08a;
        }
    </style>

    <script>
        (function() {
            var blogrPresets = {!! $presetsJson !!};

            document.addEventListener('change', function(e) {
                var selectEl = e.target;
                if (selectEl.tagName !== 'SELECT') return;
                var name = selectEl.getAttribute('name') || '';
                if (!name.endsWith('theme_preset')) return;

                var preset = selectEl.value;
                if (!preset || !blogrPresets[preset]) return;

                var colors = blogrPresets[preset];
                for (var key in colors) {
                    if (key === 'label') continue;
                    blogrSetColorField('theme_' + key, colors[key]);
                }
            }, true);

            function blogrSetColorField(fieldName, value) {
                var input = document.querySelector(
                    '.fi-fo-color-picker input[name="' + fieldName + '"], ' +
                    '.fi-fo-color-picker input[id$="' + fieldName + '"]'
                );
                if (!input) return;

                var setter = Object.getOwnPropertyDescriptor(
                    window.HTMLInputElement.prototype, 'value'
                ).set;
                setter.call(input, value);
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        })();

        function blogrRemoveHighlights(container) {
            var marks = container.querySelectorAll('.settings-highlight');
            marks.forEach(function(mark) {
                var text = document.createTextNode(mark.textContent);
                mark.parentNode.replaceChild(text, mark);
            });
        }

        function blogrHighlightText(container, term) {
            if (!term) return;
            try {
                var escaped = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                var re = new RegExp('(' + escaped + ')', 'gi');

                container.querySelectorAll('.fi-section-header-heading, .fi-section-header-description, .fi-sc-text, .fi-in-text, .fi-sc-section-label').forEach(function(el) {
                    if (el.querySelector('.settings-highlight')) return;
                    var html = el.innerHTML;
                    if (!html.toLowerCase().includes(term)) return;
                    el.innerHTML = html.replace(re, '<mark class="settings-highlight">$1</mark>');
                });

                container.querySelectorAll('.fi-fo-field-label-content').forEach(function(el) {
                    if (el.querySelector('.settings-highlight')) return;
                    if (!el.textContent.toLowerCase().includes(term)) return;
                    var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null, false);
                    var nodes = [];
                    while (walker.nextNode()) nodes.push(walker.currentNode);
                    nodes.forEach(function(textNode) {
                        if (textNode.textContent.toLowerCase().indexOf(term) === -1) return;
                        var frag = document.createDocumentFragment();
                        var parts = textNode.textContent.split(re);
                        for (var i = 0; i < parts.length; i++) {
                            if (i % 2 === 1) {
                                var mark = document.createElement('mark');
                                mark.className = 'settings-highlight';
                                mark.textContent = parts[i];
                                frag.appendChild(mark);
                            } else if (parts[i]) {
                                frag.appendChild(document.createTextNode(parts[i]));
                            }
                        }
                        textNode.parentNode.replaceChild(frag, textNode);
                    });
                });
            } catch(e) {}            
        }

        function blogrFilterSettings() {
            var searchInput = document.getElementById('blogr-settings-search');
            if (!searchInput) return;

            var q = searchInput.value.toLowerCase().trim();
            var tabsContainer = document.querySelector('.fi-sc-tabs');
            if (!tabsContainer) return;

            var tabPanels = tabsContainer.querySelectorAll('.fi-sc-tabs-tab');
            var tabButtons = tabsContainer.querySelectorAll('.fi-tabs-item[data-tab-key]');
            var totalMatches = 0;
            var noResultsEl = document.getElementById('blogr-no-results');
            var searchTermEl = document.getElementById('blogr-search-term');

            blogrRemoveHighlights(tabsContainer);

            tabPanels.forEach(function(panel, index) {
                var btn = tabButtons[index];
                var wrappers = panel.querySelectorAll('.fi-sc-section');
                var panelMatches = 0;

                wrappers.forEach(function(wrapper) {
                    if (!q) {
                        wrapper.style.removeProperty('display');
                        panelMatches++;
                    } else if (wrapper.textContent.toLowerCase().indexOf(q) !== -1) {
                        wrapper.style.removeProperty('display');
                        panelMatches++;
                    } else {
                        wrapper.style.display = 'none';
                    }
                });

                totalMatches += panelMatches;

                if (btn) {
                    btn.classList.toggle('settings-tab-no-match', q && panelMatches === 0);
                }
            });

            if (noResultsEl) {
                noResultsEl.style.display = (q && totalMatches === 0) ? '' : 'none';
            }
            if (searchTermEl) {
                searchTermEl.textContent = searchInput.value;
            }

            if (q && totalMatches > 0) {
                var activePanel = tabsContainer.querySelector('.fi-sc-tabs-tab.fi-active');
                if (activePanel) {
                    var activeWrappers = activePanel.querySelectorAll('.fi-sc-section');
                    var anyVisible = false;
                    activeWrappers.forEach(function(w) {
                        if (!w.style.display || w.style.display === '') {
                            anyVisible = true;
                        }
                    });
                    if (!anyVisible) {
                        for (var i = 0; i < tabPanels.length; i++) {
                            var wrappers = tabPanels[i].querySelectorAll('.fi-sc-section');
                            var hasMatch = false;
                            wrappers.forEach(function(w) {
                                if (w.textContent.toLowerCase().indexOf(q) !== -1) {
                                    hasMatch = true;
                                }
                            });
                            if (hasMatch && tabButtons[i]) {
                                tabButtons[i].click();
                                break;
                            }
                        }
                    }
                }
            }

            if (q) {
                tabPanels.forEach(function(panel) {
                    panel.querySelectorAll('.fi-sc-section').forEach(function(w) {
                        if (!w.style.display || w.style.display === '') {
                            blogrHighlightText(w, q);
                        }
                    });
                });
            }

            tabPanels.forEach(function(panel, index) {
                var btn = tabButtons[index];
                if (!btn) return;
                var badge = btn.querySelector('.settings-search-badge');
                if (!q) {
                    if (badge) badge.remove();
                    return;
                }
                var markCount = panel.querySelectorAll('.settings-highlight').length;
                if (markCount > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'settings-search-badge';
                        btn.appendChild(badge);
                    }
                    badge.textContent = markCount;
                } else if (badge) {
                    badge.remove();
                }
            });
        }

        (function() {
            var searchInput = document.getElementById('blogr-settings-search');
            if (!searchInput) return;

            searchInput.addEventListener('input', blogrFilterSettings);

            document.addEventListener('click', function(e) {
                var tabBtn = e.target.closest('.fi-tabs-item[data-tab-key]');
                if (tabBtn) {
                    setTimeout(blogrFilterSettings, 50);
                }
            });
        })();
    </script>
</x-filament-panels::page>
