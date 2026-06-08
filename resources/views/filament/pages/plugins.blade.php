<x-filament-panels::page>
    @php
        $extensions = $this->getExtensionsList();
        $pluginCount = count($extensions);
        $colors = ['#4f46e5', '#059669', '#d97706', '#dc2626', '#0891b2', '#7c3aed'];
        $disabledIds = $this->getDisabledExtensions();
    @endphp

    <style>
        .blogr-plugin-card {
            border-radius: 0.75rem;
            background-color: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        .dark .blogr-plugin-card {
            background-color: #1f2937;
            border-color: #374151;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        .blogr-plugin-card:last-child {
            margin-bottom: 0;
        }
        .blogr-plugin-initials {
            width: 36px;
            height: 36px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .blogr-plugin-name {
            font-weight: 600;
            color: #111827;
            font-size: 15px;
            line-height: 1.4;
        }
        .dark .blogr-plugin-name {
            color: #e5e7eb;
        }
        .blogr-plugin-version {
            font-size: 0.75rem;
            font-family: ui-monospace, monospace;
            color: #6b7280;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            padding: 0.125rem 0.375rem;
        }
        .dark .blogr-plugin-version {
            color: #9ca3af;
            background-color: #374151;
        }
        .blogr-plugin-toggle {
            flex-shrink: 0;
            width: 36px;
            height: 20px;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            position: relative;
            transition: background-color 0.2s ease;
            padding: 0;
            outline: none;
        }
        .blogr-plugin-toggle:focus-visible {
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.5);
        }
        .blogr-plugin-toggle-on {
            background-color: #059669;
        }
        .dark .blogr-plugin-toggle-on {
            background-color: #34d399;
        }
        .blogr-plugin-toggle-off {
            background-color: #d1d5db;
        }
        .dark .blogr-plugin-toggle-off {
            background-color: #4b5563;
        }
        .blogr-plugin-toggle-knob {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }
        .blogr-plugin-toggle-on .blogr-plugin-toggle-knob {
            transform: translateX(16px);
        }
        .blogr-plugin-toggle-off .blogr-plugin-toggle-knob {
            transform: translateX(0);
        }
        .blogr-plugin-toggle-core {
            cursor: default;
            font-size: 0.75rem;
            font-weight: 500;
            color: #4338ca;
            background-color: #e0e7ff;
            border-radius: 9999px;
            padding: 0.25rem 0.625rem;
            flex-shrink: 0;
        }
        .dark .blogr-plugin-toggle-core {
            color: #a5b4fc;
            background-color: #312e81;
        }
        .blogr-plugin-description {
            margin-top: 0.75rem;
            font-size: 0.875rem;
            color: #4b5563;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .dark .blogr-plugin-description {
            color: #9ca3af;
        }
        .blogr-plugin-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        .dark .blogr-plugin-meta {
            border-top-color: #374151;
        }
        .blogr-plugin-meta .flex-1 {
            flex: 1;
        }
        .blogr-plugin-meta a {
            color: #4f46e5;
            text-decoration: none;
        }
        .dark .blogr-plugin-meta a {
            color: #818cf8;
        }
        .blogr-plugin-meta a:hover {
            text-decoration: underline;
        }
        .blogr-plugin-meta .dependency {
            color: #d97706;
        }
        .blogr-plugin-meta .mono {
            font-family: ui-monospace, monospace;
        }
        .blogr-plugin-empty {
            text-align: center;
            padding: 5rem 0;
        }
        .blogr-plugin-empty p {
            margin-top: 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: #4b5563;
        }
        .dark .blogr-plugin-empty p {
            color: #9ca3af;
        }
        .blogr-plugin-empty .sub {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            font-weight: 400;
            color: #9ca3af;
        }
        .dark .blogr-plugin-empty .sub {
            color: #6b7280;
        }
        .blogr-plugin-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .blogr-plugin-row .flex-1 {
            flex: 1;
            min-width: 0;
        }
        .blogr-plugin-badges {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>

    @if($pluginCount === 0)
        <div class="blogr-plugin-empty">
            <svg width="64" height="64" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
            </svg>
            <p>{{ __('No plugins installed') }}</p>
            <p class="sub">{{ __('Plugins extend Blogr with additional features.') }}</p>
        </div>
    @else
        @foreach($extensions as $ext)
            @php
                $color = $colors[$loop->index % count($colors)];
                $isCore = $ext->getId() === 'blogr-core';
                $initials = collect(explode(' ', $ext->getName()))->take(2)->map(fn($w) => substr($w, 0, 1))->implode('');
            @endphp

            <div class="blogr-plugin-card">
                <div class="blogr-plugin-row">
                    <div class="blogr-plugin-initials" style="background-color: {{ $color }}">
                        {{ $initials }}
                    </div>

                    <div class="flex-1">
                        <div class="blogr-plugin-badges">
                            <span class="blogr-plugin-name">{{ $ext->getName() }}</span>
                            <span class="blogr-plugin-version">v{{ $ext->getVersion() }}</span>
                        </div>
                    </div>

                    @if($isCore)
                        <span class="blogr-plugin-toggle-core">Core</span>
                    @else
                        <button
                            type="button"
                            wire:click="toggleExtension('{{ $ext->getId() }}')"
                            class="blogr-plugin-toggle @if(in_array($ext->getId(), $disabledIds)) blogr-plugin-toggle-off @else blogr-plugin-toggle-on @endif"
                            role="switch"
                            aria-checked="{{ in_array($ext->getId(), $disabledIds) ? 'false' : 'true' }}"
                            aria-label="Toggle {{ $ext->getName() }}"
                            title="{{ in_array($ext->getId(), $disabledIds) ? __('Disabled') : __('Active') }}"
                        >
                            <span class="blogr-plugin-toggle-knob"></span>
                        </button>
                    @endif
                </div>

                <p class="blogr-plugin-description">{{ $ext->getDescription() }}</p>

                <div class="blogr-plugin-meta">
                    <span>by {{ $ext->getAuthor() }}</span>
                    <span>·</span>
                    <span class="mono">{{ $ext->getId() }}</span>

                    @if(!empty($ext->getDependencies()))
                        <span>·</span>
                        <span class="dependency">Requires: {{ implode(', ', $ext->getDependencies()) }}</span>
                    @endif

                    <span class="flex-1"></span>

                    @if($ext->getHomepage())
                        <a href="{{ $ext->getHomepage() }}" target="_blank" rel="noopener noreferrer">GitHub →</a>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</x-filament-panels::page>