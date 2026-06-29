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
        .blogr-plugin-settings-btn {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #9ca3af;
            transition: color 0.15s ease, background-color 0.15s ease;
            padding: 0;
            outline: none;
            text-decoration: none;
        }
        .blogr-plugin-settings-btn:hover {
            color: #4f46e5;
            background-color: #f3f4f6;
        }
        .dark .blogr-plugin-settings-btn:hover {
            color: #818cf8;
            background-color: #374151;
        }
        .blogr-plugin-settings-btn svg {
            width: 18px;
            height: 18px;
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
        .blogr-plugin-actions {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            flex-shrink: 0;
        }
        .blogr-community-section {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e5e7eb;
        }
        .dark .blogr-community-section {
            border-top-color: #374151;
        }
        .blogr-community-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.25rem;
        }
        .dark .blogr-community-title {
            color: #e5e7eb;
        }
        .blogr-community-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .dark .blogr-community-subtitle {
            color: #9ca3af;
        }
        .blogr-community-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.5rem;
        }
        .dark .blogr-community-card {
            background-color: #111827;
            border-color: #374151;
        }
        .blogr-community-card:last-child {
            margin-bottom: 0;
        }
        .blogr-community-card svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: #6b7280;
        }
        .blogr-community-card .info {
            flex: 1;
            min-width: 0;
        }
        .blogr-community-card .info .name {
            font-weight: 600;
            font-size: 0.875rem;
            color: #111827;
        }
        .dark .blogr-community-card .info .name {
            color: #e5e7eb;
        }
        .blogr-community-card .info .desc {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        .blogr-community-card .repo-link {
            font-size: 0.8125rem;
            color: #4f46e5;
            text-decoration: none;
            font-family: ui-monospace, monospace;
            flex-shrink: 0;
        }
        .dark .blogr-community-card .repo-link {
            color: #818cf8;
        }
        .blogr-community-card .repo-link:hover {
            text-decoration: underline;
        }
        .blogr-community-install-note {
            font-size: 0.8125rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .dark .blogr-community-install-note {
            color: #6b7280;
        }
        .blogr-community-dev-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }
        .dark .blogr-community-dev-link {
            color: #818cf8;
        }
        .blogr-community-dev-link:hover {
            text-decoration: underline;
        }
        .blogr-community-dev-section {
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }
        .dark .blogr-community-dev-section {
            border-top-color: #374151;
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

                    <div class="blogr-plugin-actions">
                        @php $settingsUrl = !in_array($ext->getId(), $disabledIds) ? $ext->getSettingsUrl() : null; @endphp
                        @if(!$isCore && $settingsUrl)
                            <a
                                href="{{ $settingsUrl }}"
                                class="blogr-plugin-settings-btn"
                                title="{{ __('Settings') }}"
                                aria-label="{{ __(':name settings', ['name' => $ext->getName()]) }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                        @endif

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

        @php $community = $this->getCommunityPlugins(); @endphp
        @if(count($community) > 0)
            <div class="blogr-community-section">
                <div class="blogr-community-title">{{ __('Community Plugins') }}</div>
                <div class="blogr-community-subtitle">{{ __('Discover more plugins from the Blogr community.') }}</div>
                <p class="blogr-community-install-note">{{ __('Plugins are installed manually via Composer — refer to each plugin\'s documentation for instructions.') }}</p>

                @foreach($community as $plugin)
                    <div class="blogr-community-card">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                        </svg>
                        <div class="info">
                            <div class="name">{{ $plugin['name'] }}</div>
                            @if($plugin['description'])
                                <div class="desc">{{ $plugin['description'] }}</div>
                            @endif
                        </div>
                        <a href="{{ $plugin['url'] }}" target="_blank" rel="noopener noreferrer" class="repo-link">
                            {{ $plugin['url'] }}
                        </a>
                    </div>
                @endforeach

                <div class="blogr-community-dev-section">
                    <div class="blogr-community-title" style="margin-top: 1.5rem;">{{ __('Develop a Plugin') }}</div>
                    <p class="blogr-community-install-note">{{ __('Create your own Blogr plugin by implementing the BlogrExtension interface. See the') }} <a href="https://github.com/happytodev/blogr?tab=readme-ov-file#developing-plugins" target="_blank" rel="noopener noreferrer" class="blogr-community-dev-link">{{ __('Developing Plugins guide') }}</a> {{ __('in the main Blogr README.') }}</p>
                </div>
            </div>
        @else
            <div class="blogr-community-section">
                <div class="blogr-community-title">{{ __('Develop a Plugin') }}</div>
                <p class="blogr-community-install-note">{{ __('Create your own Blogr plugin by implementing the BlogrExtension interface. See the') }} <a href="https://github.com/happytodev/blogr?tab=readme-ov-file#developing-plugins" target="_blank" rel="noopener noreferrer" class="blogr-community-dev-link">{{ __('Developing Plugins guide') }}</a> {{ __('in the main Blogr README.') }}</p>
            </div>
        @endif
    @endif
</x-filament-panels::page>