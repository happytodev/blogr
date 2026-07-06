@php
    $id = $getId();
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $key = $getKey();
    $label = $getLabel();
    $statePath = $getStatePath();
    $fileAttachmentsMaxSize = $getFileAttachmentsMaxSize();
    $fileAttachmentsAcceptedFileTypes = $getFileAttachmentsAcceptedFileTypes();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    @if ($isDisabled())
        <div id="{{ $id }}" class="fi-fo-markdown-editor fi-disabled fi-prose">
            {!! str($getState())->markdown($getCommonMarkOptions(), $getCommonMarkExtensions())->sanitizeHtml() !!}
        </div>
    @else
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :attributes="
                \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                    ->class(['fi-fo-markdown-editor'])
            "
        >
            <div
                aria-labelledby="{{ $id }}-label"
                id="{{ $id }}"
                role="group"
                x-load
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('markdown-editor', 'filament/forms') }}"
                x-data="markdownEditorFormComponent({
                            canAttachFiles: @js($hasFileAttachments()),
                            isLiveDebounced: @js($isLiveDebounced()),
                            isLiveOnBlur: @js($isLiveOnBlur()),
                            label: @js($label),
                            liveDebounce: @js($getNormalizedLiveDebounce()),
                            maxHeight: @js($getMaxHeight()),
                            minHeight: @js($getMinHeight()),
                            placeholder: @js($getPlaceholder()),
                            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
                            toolbarButtons: @js($getToolbarButtons()),
                            translations: @js(__('filament-forms::components.markdown_editor')),
                            uploadFileAttachmentUsing: async (file, onSuccess, onError) => {
                                const acceptedTypes = @js($fileAttachmentsAcceptedFileTypes)

                                if (acceptedTypes && ! acceptedTypes.includes(file.type)) {
                                    return onError(@js($fileAttachmentsAcceptedFileTypes ? __('filament-forms::components.markdown_editor.file_attachments_accepted_file_types_message', ['values' => implode(', ', $fileAttachmentsAcceptedFileTypes)]) : null))
                                }

                                const maxSize = @js($fileAttachmentsMaxSize)

                                if (maxSize && file.size > +maxSize * 1024) {
                                    return onError(@js($fileAttachmentsMaxSize ? trans_choice('filament-forms::components.markdown_editor.file_attachments_max_size_message', $fileAttachmentsMaxSize, ['max' => $fileAttachmentsMaxSize]) : null))
                                }

                                $wire.upload(`componentFileAttachments.{{ $statePath }}`, file, () => {
                                    $wire
                                        .callSchemaComponentMethod(
                                            '{{ $key }}',
                                            'saveUploadedFileAttachmentAndGetUrl',
                                        )
                                        .then((url) => {
                                            if (! url) {
                                                return onError()
                                            }

                                            onSuccess(url)
                                        })
                                })
                            },
                        })"
                wire:ignore
                {{ $getExtraAlpineAttributeBag() }}
            >
                <textarea x-ref="editor" x-cloak></textarea>
            </div>
        </x-filament::input.wrapper>
        @once
            <style>
                .editor-toolbar .callout-btn::before,
                .editor-toolbar .callout-btn::after {
                    content: none !important;
                    display: none !important;
                }
                .editor-toolbar .callout-btn svg {
                    display: block;
                    pointer-events: none;
                }
                .editor-toolbar .callout-btn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }
            </style>
        @endonce
        @php
            $calloutId = $id;
        @endphp
        <script>
            (function() {
                var check = setInterval(function() {
                    var el = document.getElementById('{{ $calloutId }}');
                    if (!el || !el._editor) return;
                    clearInterval(check);
                    var editor = el._editor;
                    var tb = editor.gui.toolbar;
                    var sep = document.createElement('div');
                    sep.className = 'separator';
                    tb.appendChild(sep);
                    var callouts = [
                        {label:'Tip', cls:'callout-btn', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>', tpl:':::tip[Lorem]\nIpsum sit amet *em dolorem*.\n:::'},
                        {label:'Info', cls:'callout-btn', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', tpl:':::info[Lorem]\nIpsum sit amet.\n:::'},
                        {label:'Caution', cls:'callout-btn', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>', tpl:':::caution[Lorem]\nIpsum sit amet.\n:::'},
                        {label:'Danger', cls:'callout-btn', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 9l6 6m0-6l-6 6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>', tpl:':::danger[Lorem]\nIpsum sit amet.\n:::'},
                    ];
                    callouts.forEach(function(c) {
                        var btn = document.createElement('button');
                        btn.className = c.cls;
                        btn.innerHTML = c.icon;
                        btn.setAttribute('aria-label', 'Insert ' + c.label.toLowerCase() + ' callout');
                        btn.setAttribute('title', c.label);
                        btn.addEventListener('click', function() {
                            editor.codemirror.replaceSelection(c.tpl);
                        });
                        tb.appendChild(btn);
                    });
                }, 50);
            })();
        </script>
    @endif
</x-dynamic-component>
