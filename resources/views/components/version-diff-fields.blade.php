@php
    $stripUuidKeys = function ($data) use (&$stripUuidKeys) {
        if (!is_array($data)) return $data;

        // FileUpload wrapper: {uuid: "path"} → "path"
        $keys = array_keys($data);
        if (count($data) === 1 && preg_match('/^[a-f0-9-]{36}$/', (string) $keys[0]) && is_string($data[$keys[0]])) {
            return $data[$keys[0]];
        }

        // Repeater items with all-UUID keys: {uuid: {...}, uuid: {...}} → [{...}, {...}]
        if (!array_is_list($data)) {
            $allUuid = array_reduce($keys, fn ($c, $k) => $c && preg_match('/^[a-f0-9-]{36}$/', (string) $k), true);
            if ($allUuid) {
                return array_values(array_map($stripUuidKeys, $data));
            }
        }

        // Recurse
        return array_map($stripUuidKeys, $data);
    };
@endphp

@if($key === 'blocks')
    @php
        $oldBlocks = is_array($oldVal) ? $oldVal : (json_decode($oldVal, true) ?? []);
        $newBlocks = is_array($newVal) ? $newVal : (json_decode($newVal, true) ?? []);
        $oldIndexed = array_values($oldBlocks);
        $newIndexed = array_values($newBlocks);
        $maxBlockCount = max(count($oldIndexed), count($newIndexed));
    @endphp
    <div class="mt-1 text-xs text-gray-700 dark:text-gray-300 space-y-1.5">
        <span class="text-gray-500">{{ count($oldBlocks) }} → {{ count($newBlocks) }} blocks</span>
        @for($i = 0; $i < $maxBlockCount; $i++)
            @php
                $oldB = $oldIndexed[$i] ?? null;
                $newB = $newIndexed[$i] ?? null;
                $oldType = $oldB['type'] ?? '';
                $newType = $newB['type'] ?? '';
                $LONG_TEXT_THRESHOLD = 120;
            @endphp
            @if($oldB === null && $newB !== null)
                <div class="flex items-center gap-1 text-green-700 dark:text-green-400">
                    <span class="font-medium">+</span>
                    <span>Block #{{ $i + 1 }} <span class="bg-green-100 dark:bg-green-900/30 px-1 rounded">{{ $newType }}</span> added</span>
                </div>
            @elseif($oldB !== null && $newB === null)
                <div class="flex items-center gap-1 text-red-600 dark:text-red-400">
                    <span class="font-medium">−</span>
                    <span>Block #{{ $i + 1 }} <span class="bg-red-100 dark:bg-red-900/30 px-1 rounded">{{ $oldType }}</span> removed</span>
                </div>
            @elseif(json_encode($stripUuidKeys($oldB)) !== json_encode($stripUuidKeys($newB)))
                @php
                    $oldData = $oldB['data'] ?? [];
                    $newData = $newB['data'] ?? [];
                    $dataChanges = [];
                    foreach (array_keys($oldData + $newData) as $k) {
                        $oldV = $oldData[$k] ?? null;
                        $newV = $newData[$k] ?? null;
                        if (json_encode($stripUuidKeys($oldV)) !== json_encode($stripUuidKeys($newV))) {
                            $dataChanges[$k] = ['old' => $stripUuidKeys($oldV), 'new' => $stripUuidKeys($newV)];
                        }
                    }
                @endphp
                @if(!empty($dataChanges))
                <div class="border border-indigo-200 dark:border-indigo-800/40 rounded-md overflow-hidden">
                    <div class="bg-indigo-50 dark:bg-indigo-900/15 px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border-b border-indigo-200 dark:border-indigo-800/40">
                        Block #{{ $i + 1 }} ({{ $newType }})
                    </div>
                    <div class="px-2 py-1 space-y-1 bg-white dark:bg-gray-800/40">
                        @foreach($dataChanges as $fieldName => $vals)
                            @php
                                $oldV = $vals['old'] ?? '';
                                $newV = $vals['new'] ?? '';
                                $oldVStr = is_array($oldV) ? json_encode($oldV, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $oldV;
                                $newVStr = is_array($newV) ? json_encode($newV, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $newV;
                                $isLong = mb_strlen($oldVStr) > $LONG_TEXT_THRESHOLD || mb_strlen($newVStr) > $LONG_TEXT_THRESHOLD;
                            @endphp
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">{{ $fieldName }}</p>
                                @if($isLong)
                                    @php
                                        $oldLines = preg_split('/\R/', $oldVStr);
                                        $newLines = preg_split('/\R/', $newVStr);
                                        $maxOld = count($oldLines);
                                        $maxNew = count($newLines);
                                        $maxLines = max($maxOld, $maxNew);
                                        $firstDiff = 0;
                                        while ($firstDiff < $maxLines && ($oldLines[$firstDiff] ?? '') === ($newLines[$firstDiff] ?? '')) { $firstDiff++; }
                                        $lastDiff = $maxLines - 1;
                                        while ($lastDiff >= $firstDiff && ($oldLines[$lastDiff] ?? '') === ($newLines[$lastDiff] ?? '')) { $lastDiff--; }
                                        $context = 2;
                                        $showStart = max(0, $firstDiff - $context);
                                        $showEnd = min($maxLines - 1, $lastDiff + $context);
                                    @endphp
                                    <div class="rounded overflow-hidden border border-gray-200 dark:border-gray-600">
                                        <div class="bg-gray-50 dark:bg-gray-800/60 px-2 py-0.5 text-xs text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                            @php $diff = mb_strlen($newVStr) - mb_strlen($oldVStr); @endphp
                                            {{ $diff >= 0 ? '▲' : '▼' }} {{ abs($diff) }} chars
                                            @if($maxOld !== $maxNew)
                                                · {{ $maxNew - $maxOld >= 0 ? '+' : '' }}{{ $maxNew - $maxOld }} lines
                                            @endif
                                        </div>
                                        <div class="max-h-32 overflow-y-auto text-xs font-mono leading-5 bg-white dark:bg-gray-900/50">
                                            @for($l = $showStart; $l <= $showEnd; $l++)
                                                @php
                                                    $lnOld = $oldLines[$l] ?? null;
                                                    $lnNew = $newLines[$l] ?? null;
                                                    $removed = $lnOld !== null && ($lnNew === null || $lnNew !== $lnOld);
                                                    $added = $lnNew !== null && ($lnOld === null || $lnOld !== $lnNew);
                                                @endphp
                                                @if($removed && $added)
                                                    <div class="px-2 py-0.5 text-red-700 bg-red-50 border-b border-red-100 flex gap-2"><span class="select-none w-4 text-center">−</span><span class="truncate">{{ Str::limit((string)$lnOld, 120) }}</span></div>
                                                    <div class="px-2 py-0.5 text-green-700 bg-green-50 border-b border-green-100 flex gap-2"><span class="select-none w-4 text-center">+</span><span class="truncate">{{ Str::limit((string)$lnNew, 120) }}</span></div>
                                                @elseif($removed)
                                                    <div class="px-2 py-0.5 text-red-600 bg-red-50/50 border-b border-red-100 flex gap-2"><span class="select-none w-4 text-center">−</span><span class="truncate">{{ Str::limit((string)$lnOld, 120) }}</span></div>
                                                @elseif($added)
                                                    <div class="px-2 py-0.5 text-green-700 bg-green-50 border-b border-green-100 flex gap-2"><span class="select-none w-4 text-center">+</span><span class="truncate">{{ Str::limit((string)$lnNew, 120) }}</span></div>
                                                @else
                                                    <div class="px-2 py-0.5 text-gray-400 border-b border-gray-100 flex gap-2"><span class="select-none w-4 text-center">·</span><span class="truncate">{{ Str::limit((string)($lnOld ?? $lnNew), 120) }}</span></div>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols-[1fr_auto_1fr] gap-2 items-center text-xs mt-0.5">
                                        <span class="text-gray-400 dark:text-gray-500 line-through truncate">{{ Str::limit($oldVStr, 50) }}</span>
                                        <span class="text-gray-300 dark:text-gray-500 font-mono">→</span>
                                        <span class="text-gray-800 dark:text-gray-200 truncate">{{ Str::limit($newVStr, 50) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @endif
        @endfor
    </div>
@elseif(in_array($key, ['tldr', 'content']))
    @php
        $oldLines = preg_split('/\R/', $oldVal);
        $newLines = preg_split('/\R/', $newVal);
        $maxOld = count($oldLines);
        $maxNew = count($newLines);
        $maxLines = max($maxOld, $maxNew);
        $firstDiff = 0;
        while ($firstDiff < $maxLines && ($oldLines[$firstDiff] ?? '') === ($newLines[$firstDiff] ?? '')) { $firstDiff++; }
        $lastDiff = $maxLines - 1;
        while ($lastDiff >= $firstDiff && ($oldLines[$lastDiff] ?? '') === ($newLines[$lastDiff] ?? '')) { $lastDiff--; }
        $context = 2;
        $showStart = max(0, $firstDiff - $context);
        $showEnd = min($maxLines - 1, $lastDiff + $context);
    @endphp
    <div class="mt-1 rounded-md overflow-hidden border border-gray-200 dark:border-gray-600">
        <div class="bg-gray-50 dark:bg-gray-800/60 px-2 py-1 text-xs text-gray-400 border-b border-gray-200 dark:border-gray-600">
            @php $diff = mb_strlen($newVal) - mb_strlen($oldVal); @endphp
            {{ $diff >= 0 ? '▲' : '▼' }} {{ abs($diff) }} chars
            @if($maxOld !== $maxNew)
                · {{ $maxNew - $maxOld >= 0 ? '+' : '' }}{{ $maxNew - $maxOld }} lines
            @endif
        </div>
        <div class="max-h-48 overflow-y-auto text-xs font-mono leading-5">
            @for($i = $showStart; $i <= $showEnd; $i++)
                @php
                    $oldLine = $oldLines[$i] ?? null;
                    $newLine = $newLines[$i] ?? null;
                    $removed = $oldLine !== null && ($newLine === null || $newLine !== $oldLine);
                    $added = $newLine !== null && ($oldLine === null || $oldLine !== $newLine);
                @endphp
                @if($removed && $added)
                    <div class="px-2 py-0.5 text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900/30 flex gap-2">
                        <span class="select-none flex-shrink-0 w-4 text-center">−</span>
                        <span class="truncate">{{ Str::limit($oldLine, 120) }}</span>
                    </div>
                    <div class="px-2 py-0.5 text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-900/30 flex gap-2">
                        <span class="select-none flex-shrink-0 w-4 text-center">+</span>
                        <span class="truncate">{{ Str::limit($newLine, 120) }}</span>
                    </div>
                @elseif($removed)
                    <div class="px-2 py-0.5 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-900/20 flex gap-2">
                        <span class="select-none flex-shrink-0 w-4 text-center">−</span>
                        <span class="truncate">{{ Str::limit($oldLine, 120) }}</span>
                    </div>
                @elseif($added)
                    <div class="px-2 py-0.5 text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-900/30 flex gap-2">
                        <span class="select-none flex-shrink-0 w-4 text-center">+</span>
                        <span class="truncate">{{ Str::limit($newLine, 120) }}</span>
                    </div>
                @else
                    <div class="px-2 py-0.5 text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700/20 flex gap-2">
                        <span class="select-none flex-shrink-0 w-4 text-center">·</span>
                        <span class="truncate">{{ Str::limit($oldLine ?? $newLine, 120) }}</span>
                    </div>
                @endif
            @endfor
        </div>
    </div>
@else
    <div class="grid grid-cols-[1fr_auto_1fr] gap-2 items-center text-xs mt-0.5">
        <span class="text-gray-400 line-through truncate">{{ Str::limit($oldVal, 50) }}</span>
        <span class="text-gray-300 dark:text-gray-500 font-mono">→</span>
        <span class="text-gray-800 dark:text-gray-200 truncate">{{ Str::limit($newVal, 50) }}</span>
    </div>
@endif
