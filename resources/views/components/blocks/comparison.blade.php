{{--
    Bloc `comparison` — un tableau d'options côte à côte, une ligne par critère.

    Deux familles de données à ne pas confondre :
    - les listes (columns, rows, cells, ctas, ahead.items) peuvent revenir de
      l'éditeur de contenu avec des clés UUID une fois le bloc retouché dans
      l'admin. On ne lit donc jamais une clé comme une position : array_values().
    - rows[i].cells[j] se lit en regard de columns[j]. C'est un tableau au sens
      strict : la vue aligne les cellules sur les en-têtes dans l'ordre donné.
--}}
@php
    $eyebrow = $data['eyebrow'] ?? null;
    $heading = $data['heading'] ?? null;
    $intro = $data['intro'] ?? null;
    $verdictLabel = $data['verdict_label'] ?? null;
    $columns = array_values($data['columns'] ?? []);
    $rows = array_values($data['rows'] ?? []);
    $ahead = $data['ahead'] ?? [];
    $aheadTitle = is_array($ahead) ? ($ahead['title'] ?? null) : null;
    $aheadItems = is_array($ahead) ? array_values($ahead['items'] ?? []) : [];
    $closing = $data['closing'] ?? null;
    $footnote = $data['footnote'] ?? null;
    $stacked = (bool) ($data['mobile_tabs'] ?? false);
    $ctas = array_values($data['ctas'] ?? []);
    $stackedClasses = $stacked ? 'block md:table' : 'table';
@endphp

<section class="mx-auto max-w-7xl px-5 py-16 sm:py-24 lg:px-8">
    <div class="mx-auto max-w-3xl text-center">
        @if($eyebrow)
            <p class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $eyebrow }}</p>
        @endif

        @if($heading)
            <h2 class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white">{{ $heading }}</h2>
        @endif

        @if($intro)
            <p class="mt-4 text-base text-gray-600 dark:text-gray-300">{{ $intro }}</p>
        @endif
    </div>

    @if(count($columns))
        <div class="mt-12 overflow-x-auto">
            <table class="{{ $stackedClasses }} w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th scope="col" class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ $verdictLabel ?? __('Criterion') }}
                        </th>
                        @foreach($columns as $column)
                            <th scope="col" class="px-4 py-3 text-sm font-semibold {{ ($column['highlight'] ?? false) ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300' }}">
                                <span class="flex flex-wrap items-center gap-2">
                                    {{ $column['name'] ?? '' }}
                                    @if(!empty($column['badge']))
                                        <span class="rounded-full bg-gray-900 px-2 py-0.5 text-[11px] font-medium text-white dark:bg-white dark:text-gray-900">{{ $column['badge'] }}</span>
                                    @endif
                                </span>
                                @if(!empty($column['tagline']))
                                    <span class="mt-1 block text-xs font-normal text-gray-500 dark:text-gray-400">{{ $column['tagline'] }}</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th scope="row" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $row['label'] ?? '' }}
                            </th>
                            @foreach(array_values($row['cells'] ?? []) as $cell)
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($aheadTitle || count($aheadItems))
        <div class="mx-auto mt-12 max-w-3xl rounded-2xl border border-gray-200 p-6 dark:border-gray-700">
            @if($aheadTitle)
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $aheadTitle }}</h3>
            @endif

            @if(count($aheadItems))
                <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    @foreach($aheadItems as $item)
                        <li class="flex gap-2">
                            <span aria-hidden="true">—</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if($closing)
        <p class="mx-auto mt-8 max-w-3xl text-center text-base text-gray-700 dark:text-gray-200">{{ $closing }}</p>
    @endif

    @if(count($ctas))
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @foreach($ctas as $cta)
                @continue(empty($cta['text']) || empty($cta['url']))
                <a href="{{ $cta['url'] }}"
                   @if(str_starts_with($cta['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif
                   class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm font-medium {{ ($cta['style'] ?? 'primary') === 'primary' ? 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100' : 'border border-gray-300 text-gray-900 hover:bg-gray-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-800' }}">
                    {{ $cta['text'] }}
                </a>
            @endforeach
        </div>
    @endif

    @if($footnote)
        <p class="mt-6 text-center font-mono text-[11.5px] text-gray-500 dark:text-gray-400">{{ $footnote }}</p>
    @endif
</section>
