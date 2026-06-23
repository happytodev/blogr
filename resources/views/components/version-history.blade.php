<div class="space-y-3 max-h-96 overflow-y-auto">
    @forelse($history as $entry)
        @if(($entry['type'] ?? '') === 'draft')
            @php $isMultiLocale = !empty($entry['locale_fields']); @endphp
            <div x-data="{ open: false }"
                 wire:key="draft-{{ $entry['created_at'] }}"
                 class="rounded-lg border border-amber-200 dark:border-amber-800 overflow-hidden">
                <div @click="open = !open"
                     class="flex items-start gap-4 p-4 bg-amber-50 dark:bg-amber-900/20 cursor-pointer hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-200 dark:bg-amber-700 flex items-center justify-center text-sm font-bold text-amber-700 dark:text-amber-300">
                        D
                    </div>
                    <div class="flex-grow min-w-0 pt-0.5">
                        <p class="text-sm font-medium text-amber-900 dark:text-amber-100 truncate">
                            {{ $entry['title'] ?? 'Untitled' }}
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                            Draft — {{ \Carbon\Carbon::parse($entry['created_at'])->format('d/m/Y H:i') }}
                        </p>
                        @if(!empty($entry['changes']) && $entry['changes'][0] !== 'initial')
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($entry['changes'] as $field)
                                    @php
                                        $labels = [
                                            'title' => 'Title', 'slug' => 'Slug',
                                            'content' => 'Content', 'tldr' => 'TL;DR',
                                            'blocks' => 'Blocks',
                                            'seo_title' => 'SEO Title', 'seo_description' => 'SEO Desc',
                                            'seo_keywords' => 'SEO Keywords',
                                        ];
                                    @endphp
                                    <span class="text-xs px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-medium">
                                        {{ $labels[$field] ?? $field }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif(!empty($entry['changes']) && $entry['changes'][0] === 'initial')
                            <span class="text-xs text-amber-500 dark:text-amber-400 mt-1 italic block">Draft from scratch</span>
                        @endif
                    </div>
                    <div class="flex-shrink-0 flex items-start gap-2 pt-0.5">
                        <span x-show="!open" class="text-amber-400 pt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                        <span x-show="open" class="text-amber-400 pt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </span>
                    </div>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-4 pb-4 pt-1 bg-amber-50/50 dark:bg-amber-900/10 border-t border-amber-100 dark:border-amber-800/50">
                    @php
                        $fields = $entry['fields'] ?? [];
                        $prev = $entry['previous_fields'] ?? null;
                        $diffLabels = [
                            'title' => 'Title',
                            'slug' => 'Slug',
                            'content' => 'Content',
                            'tldr' => 'TL;DR',
                            'blocks' => 'Blocks',
                            'seo_title' => 'SEO Title',
                            'seo_description' => 'SEO Description',
                            'seo_keywords' => 'SEO Keywords',
                        ];
                    @endphp

                    @if($prev)
                        @if($isMultiLocale)
                            @foreach($fields as $locale => $localeFields)
                                @php $localePrev = $prev[$locale] ?? []; @endphp
                                @foreach($diffLabels as $key => $label)
                                    @php
                                        $oldVal = $localePrev[$key] ?? '';
                                        $newVal = $localeFields[$key] ?? '';
                                        $changed = is_array($oldVal) || is_array($newVal)
                                            ? json_encode($oldVal) !== json_encode($newVal)
                                            : $oldVal !== $newVal;
                                    @endphp
                                    @if($changed)
                                        <div class="py-1.5 border-b border-amber-100 dark:border-amber-800/30 last:border-0">
                                            <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-0.5 flex items-center gap-1.5">
                                                <span class="text-xs px-1 py-0.5 rounded bg-amber-200/60 dark:bg-amber-700/40 text-amber-700 dark:text-amber-300 font-medium">{{ strtoupper($locale) }}</span>
                                                {{ $label }}
                                            </p>
                                            @include('blogr::components.version-diff-fields', ['key' => $key, 'oldVal' => $oldVal, 'newVal' => $newVal])
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        @else
                            @foreach($diffLabels as $key => $label)
                                @php
                                    $oldVal = $prev[$key] ?? '';
                                    $newVal = $fields[$key] ?? '';
                                    $changed = is_array($oldVal) || is_array($newVal)
                                        ? json_encode($oldVal) !== json_encode($newVal)
                                        : $oldVal !== $newVal;
                                @endphp
                                @if($changed)
                                    <div class="py-1.5 border-b border-amber-100 dark:border-amber-800/30 last:border-0">
                                        <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-0.5">{{ $label }}</p>
                                        @include('blogr::components.version-diff-fields', ['key' => $key, 'oldVal' => $oldVal, 'newVal' => $newVal])
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @else
                        <p class="text-xs text-amber-500 dark:text-amber-400 italic">Draft from scratch — no published version to compare.</p>
                    @endif
                </div>
            </div>
        @else
            <div x-data="{ open: false }"
                 wire:key="version-{{ $entry['version_id'] ?? $entry['created_at'] }}"
                 class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div @click="open = !open"
                     class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-900 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-mono font-semibold text-gray-600 dark:text-gray-400">
                        {{ $entry['version_number'] ?? '?' }}
                    </div>
                    <div class="flex-grow min-w-0 pt-0.5">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $entry['title'] ?? 'Untitled' }}
                            </p>
                            @if(isset($entry['locale']) && strlen($entry['locale']) === 2)
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-300/40 dark:bg-gray-600/40 text-gray-500 dark:text-gray-400 font-medium">
                                    {{ strtoupper($entry['locale']) }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $entry['created_at'] ? \Carbon\Carbon::parse($entry['created_at'])->format('d/m/Y H:i') : 'Unknown date' }}
                        </p>
                        @if(!empty($entry['changes']) && $entry['changes'][0] !== 'initial')
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($entry['changes'] as $field)
                                    @php
                                        $labels = [
                                            'title' => 'Title', 'slug' => 'Slug',
                                            'content' => 'Content', 'tldr' => 'TL;DR',
                                            'blocks' => 'Blocks',
                                            'seo_title' => 'SEO Title', 'seo_description' => 'SEO Desc',
                                            'seo_keywords' => 'SEO Keywords',
                                        ];
                                    @endphp
                                    <span class="text-xs px-1.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-medium">
                                        {{ $labels[$field] ?? $field }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif(!empty($entry['changes']) && $entry['changes'][0] === 'initial')
                            <span class="text-xs text-gray-400 dark:text-gray-500 mt-1 italic block">
                                Initial version
                            </span>
                        @endif
                    </div>
                    <div class="flex-shrink-0 flex items-start gap-2 pt-0.5">
                        <button wire:click="restoreVersion({{ $entry['version_id'] }})"
                                class="px-2.5 py-1 text-xs font-medium rounded-md bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm transition-colors">
                            Restore
                        </button>
                        <span x-show="!open" class="text-gray-400 pt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                        <span x-show="open" class="text-gray-400 pt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </span>
                    </div>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-4 pb-4 pt-1 bg-white dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700/50">
                    @php
                        $fields = $entry['fields'] ?? [];
                        $prev = $entry['previous_fields'] ?? null;
                        $diffLabels = [
                            'title' => 'Title',
                            'slug' => 'Slug',
                            'content' => 'Content',
                            'tldr' => 'TL;DR',
                            'blocks' => 'Blocks',
                            'seo_title' => 'SEO Title',
                            'seo_description' => 'SEO Description',
                            'seo_keywords' => 'SEO Keywords',
                        ];
                    @endphp

                    @if($prev)
                        @foreach($diffLabels as $key => $label)
                            @php
                                $oldVal = $prev[$key] ?? '';
                                $newVal = $fields[$key] ?? '';
                                $changed = is_array($oldVal) || is_array($newVal)
                                    ? json_encode($oldVal) !== json_encode($newVal)
                                    : $oldVal !== $newVal;
                            @endphp
                            @if($changed)
                                <div class="py-1.5 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">{{ $label }}</p>
                                    @include('blogr::components.version-diff-fields', ['key' => $key, 'oldVal' => $oldVal, 'newVal' => $newVal])
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">Initial version — no previous data to compare.</p>
                    @endif
                </div>
            </div>
        @endif
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
            No history available for this post.
        </p>
    @endforelse
</div>
