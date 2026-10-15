{{--
    `install` block — numbered steps, one command per platform, and a
    download call.

    Steps and platforms are lists: the content editor
    rewrites them with UUID keys once the block is edited in the admin, so we
    never read the key as a position (array_values), and we pass the
    command to Alpine with @js() rather than json_encode().
--}}
@php
    $eyebrow = $data['eyebrow'] ?? null;
    $heading = $data['heading'] ?? null;
    $steps = array_values($data['steps'] ?? []);
    $rows = array_values($data['rows'] ?? []);
    $copyLabel = $data['copy'] ?? __('Copy');
    $copiedLabel = $data['copied'] ?? __('Copied');
    $downloadLabel = $data['download_label'] ?? null;
    $downloadText = $data['download_text'] ?? null;
    $downloadUrl = $data['download_url'] ?? null;
    $after = $data['after'] ?? null;
@endphp

<section class="mx-auto max-w-7xl px-5 py-16 sm:py-24 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
        @if($eyebrow)
            <p class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $eyebrow }}</p>
        @endif

        @if($heading)
            <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white">{{ $heading }}</h2>
        @endif
    </div>

    @if(count($steps))
        <ol class="mx-auto mt-12 max-w-3xl space-y-6">
            @foreach($steps as $index => $step)
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white dark:bg-white dark:text-gray-900">
                        {{ $index + 1 }}
                    </span>

                    <div class="min-w-0 flex-1" x-data="{ copied: false }">
                        <div class="flex items-start gap-2">
                            <code class="min-w-0 flex-1 overflow-x-auto rounded-lg bg-gray-900 px-4 py-3 font-mono text-[13px] text-gray-100">{{ $step['cmd'] ?? '' }}</code>

                            <button type="button"
                                    x-on:click="navigator.clipboard.writeText(@js($step['cmd'] ?? '')); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="shrink-0 rounded-lg border border-gray-300 px-3 py-3 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                                    aria-live="polite">
                                <span x-show="!copied">{{ $copyLabel }}</span>
                                <span x-show="copied" x-cloak>{{ $copiedLabel }}</span>
                            </button>
                        </div>

                        @if(!empty($step['note']))
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $step['note'] }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif

    @if(count($rows))
        <div class="mx-auto mt-10 max-w-3xl overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700">
            <table class="w-full border-collapse text-left">
                <tbody>
                    @foreach($rows as $row)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <th scope="row" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $row['os'] ?? '' }}</th>
                            <td class="px-4 py-3 font-mono text-[13px] text-gray-700 dark:text-gray-200">{{ $row['cmd'] ?? '' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $row['hint'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($downloadUrl)
        <div class="mt-10 text-center">
            <a href="{{ $downloadUrl }}"
               class="inline-flex items-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                {{ $downloadLabel ?: __('Download') }}
            </a>

            @if($downloadText)
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $downloadText }}</p>
            @endif
        </div>
    @endif

    @if($after)
        <p class="mx-auto mt-8 max-w-3xl text-center text-sm text-gray-600 dark:text-gray-300">{{ $after }}</p>
    @endif
</section>
