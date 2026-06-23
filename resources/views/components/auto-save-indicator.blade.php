@php
    $interval = config('blogr.auto_save_interval', 30);
@endphp

@if($interval > 0)
    @script
        <script>
            let timer = setInterval(() => {
                $wire.autoSave();
            }, {{ $interval * 1000 }});

            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && (e.key === 's' || e.keyCode === 83)) {
                    e.preventDefault();
                    $wire.manualSave();
                }
            });

            window.addEventListener('beforeunload', async (e) => {
                if ($wire.hasUnsavedChanges) {
                    await $wire.autoSave();
                    if ($wire.hasUnsavedChanges) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                }
            });

            let attempts = 0;
            let el = null;
            let lastSavedAt = null;
            let lastManualAt = null;
            let hasUnsaved = false;
            let activeDraft = false;

            let ensureInDOM = () => {
                if (el && document.getElementById('blogr-auto-save-indicator')) return el;
                let bar = null;
                document.querySelectorAll('.fi-ac').forEach(e => {
                    if (e.closest('.fi-header-actions-ctn') || e.closest('header')) return;
                    if (! bar) bar = e;
                });
                if (! bar) return null;
                el = document.createElement('span');
                el.id = 'blogr-auto-save-indicator';
                el.className = 'ml-auto inline-flex items-center gap-1 px-3 text-xs whitespace-nowrap';
                el.style.display = 'inline-flex';
                bar.appendChild(el);
                return el;
            };

            let formatDate = (iso) => {
                let d = new Date(iso);
                return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
            };

            let updateIndicator = () => {
                if (! ensureInDOM()) return;
                el.classList.remove('text-amber-600', 'dark:text-amber-400', 'text-gray-500', 'dark:text-gray-400', 'text-sky-600', 'dark:text-sky-400');
                if (hasUnsaved) {
                    el.classList.add('text-amber-600', 'dark:text-amber-400');
                    el.innerHTML = '✏️ {{ __('blogr::blogr.auto_save.unsaved') }}';
                } else if (lastManualAt) {
                    el.classList.add('text-gray-500', 'dark:text-gray-400');
                    el.innerHTML = '💾 {{ __('blogr::blogr.auto_save.manually_saved_at') }} ' + formatDate(lastManualAt);
                } else if (lastSavedAt) {
                    el.classList.add('text-gray-500', 'dark:text-gray-400');
                    el.innerHTML = '💾 {{ __('blogr::blogr.auto_save.saved_at') }} ' + formatDate(lastSavedAt);
                } else if (activeDraft) {
                    el.classList.add('text-sky-600', 'dark:text-sky-400');
                    el.innerHTML = '📄 Draft';
                }
            };

            let syncFromWire = () => {
                let wHasChanges = $wire.hasUnsavedChanges;
                let wLastSave = $wire.lastAutoSaveAt;
                let wManualSave = $wire.lastManualSaveAt;
                let wHasDraft = $wire.hasActiveDraft;
                if (el !== ensureInDOM() || hasUnsaved !== wHasChanges || lastSavedAt !== wLastSave || lastManualAt !== wManualSave || activeDraft !== wHasDraft) {
                    hasUnsaved = wHasChanges;
                    lastSavedAt = wLastSave;
                    lastManualAt = wManualSave;
                    activeDraft = wHasDraft;
                    updateIndicator();
                }
            };

            let initIndicator = () => {
                if (ensureInDOM()) {
                    syncFromWire();
                    setInterval(syncFromWire, 1000);
                    $wire.on('auto-saved', () => { syncFromWire(); });
                    $wire.on('manual-saved', () => { syncFromWire(); });
                } else if (attempts < 20) {
                    attempts++;
                    setTimeout(initIndicator, 200);
                }
            };

            setTimeout(initIndicator, 500);
        </script>
    @endscript
@endif
