@props(['data'])

@php
    $heading = $data['heading'] ?? null;
    $description = $data['description'] ?? null;
    $members = $data['members'] ?? [];
    $columns = $data['columns'] ?? '3';
    
    $gridCols = match($columns) {
        '2' => 'grid-cols-1 sm:grid-cols-2',
        '3' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        '4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    };
@endphp

<x-blogr::background-wrapper :data="$data">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($heading || $description)
            <div class="text-center mb-12">
                @if($heading)
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                        {{ $heading }}
                    </h2>
                @endif
                
                @if($description)
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        @if(count($members) > 0)
            <div class="grid {{ $gridCols }} gap-8">
                @foreach($members as $member)
                    <div class="group">
                        <!-- Photo -->
                        @if(!empty($member['photo']))
                            <div class="relative mb-4 overflow-hidden rounded-2xl aspect-square">
                                <img 
                                    src="{{ Storage::url($member['photo']) }}" 
                                    alt="{{ $member['name'] ?? 'Team member' }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                >
                            </div>
                        @else
                            <!-- Placeholder avatar -->
                            <div class="relative mb-4 aspect-square rounded-2xl bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                                </svg>
                            </div>
                        @endif

                        <!-- Info -->
                        <div class="text-center">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                {{ $member['name'] ?? 'Unknown' }}
                            </h3>
                            
                            @if(!empty($member['role']))
                                <p class="text-sm font-medium text-primary-600 dark:text-primary-400 mb-3">
                                    {{ $member['role'] }}
                                </p>
                            @endif

                            @if(!empty($member['bio']))
                                <p class="subtitle text-sm mb-4">
                                    {{ $member['bio'] }}
                                </p>
                            @endif

                            <!-- Social Links -->
                            @if(!empty($member['linkedin']) || !empty($member['twitter']) || !empty($member['email']))
                                <div class="flex items-center justify-center gap-3">
                                    @if(!empty($member['linkedin']))
                                        <a 
                                            href="{{ $member['linkedin'] }}" 
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors"
                                            aria-label="LinkedIn"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(!empty($member['twitter']))
                                        <a 
                                            href="{{ $member['twitter'] }}" 
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors"
                                            aria-label="Twitter/X"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(!empty($member['email']))
                                        <a 
                                            href="mailto:{{ $member['email'] }}" 
                                            class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors"
                                            aria-label="Email"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-blogr::background-wrapper>
