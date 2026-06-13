@php
    $data = json_decode($getContent(), true);
    $providerLabel = $data['provider_label'] ?? '';
    $used = $data['used'] ?? 0;
    $limit = $data['limit'] ?? null;
    $hasLimit = $data['has_limit'] ?? false;
    $remaining = $data['remaining'] ?? null;
    $percentage = $data['percentage'] ?? null;
    $period = $data['period'] ?? '';
    $provider = $data['provider'] ?? '';
@endphp

<style>
    .blogr-usage-card { background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; }
    .dark .blogr-usage-card { background-color: #1f2937; border-color: #374151; }
    .blogr-usage-track { width: 100%; height: 12px; background-color: #e5e7eb; border-radius: 9999px; overflow: hidden; margin-bottom: 6px; }
    .dark .blogr-usage-track { background-color: #374151; }
    .blogr-usage-fill { height: 12px; border-radius: 9999px; transition: width 0.5s; min-width: 8px; }
    .blogr-usage-label { font-size: 14px; font-weight: 500; color: #111827; }
    .dark .blogr-usage-label { color: #f3f4f6; }
    .blogr-usage-value { font-size: 14px; color: #4b5563; }
    .dark .blogr-usage-value { color: #9ca3af; }
    .blogr-usage-meta { font-size: 12px; color: #6b7280; }
    .dark .blogr-usage-meta { color: #9ca3af; }
    .blogr-usage-help { font-size: 12px; color: #9ca3af; margin-top: 4px; }
    .dark .blogr-usage-help { color: #6b7280; }
    .blogr-usage-note { font-size: 12px; color: #9ca3af; margin-top: 8px; font-style: italic; }
    .dark .blogr-usage-note { color: #6b7280; }
    .blogr-usage-link { font-size: 12px; color: #2563eb; text-decoration: underline; }
    .dark .blogr-usage-link { color: #60a5fa; }
</style>

<div class="blogr-usage-card">
    <div class="flex items-center justify-between" style="margin-bottom: 8px;">
        <span class="blogr-usage-label">
            {{ $providerLabel }}
        </span>
        @if($hasLimit)
            <span class="blogr-usage-value">
                {{ number_format($used) }} / {{ number_format($limit) }} @lang('blogr::blogr.translation.chars')
            </span>
        @else
            <span class="blogr-usage-value">
                {{ number_format($used) }} @lang('blogr::blogr.translation.chars')
            </span>
        @endif
    </div>

    @if($hasLimit)
        @php
            $barWidth = min($percentage, 100);
            $barHex = match(true) {
                $percentage > 80 => '#ef4444',
                $percentage > 50 => '#f97316',
                $percentage > 20 => '#eab308',
                default => '#10b981',
            };
        @endphp

        <div class="blogr-usage-track">
            <div class="blogr-usage-fill" style="width: {{ $barWidth }}%; background-color: {{ $barHex }};"></div>
        </div>

        <div class="flex items-center justify-between">
            <span class="blogr-usage-meta">
                {{ $period }} — {{ number_format($remaining) }} @lang('blogr::blogr.translation.remaining')
            </span>
            <span class="blogr-usage-meta">
                {{ $percentage }}%
            </span>
        </div>

        @if($provider === 'azure')
            <div style="margin-top: 8px;">
                <a href="https://portal.azure.com/#view/HubsExtension/BrowseResource/resourceType/Microsoft.CognitiveServices%2Faccounts" target="_blank" rel="noopener noreferrer" class="blogr-usage-link">
                    @lang('blogr::blogr.translation.view_azure_usage')
                </a>
                <p class="blogr-usage-help">
                    {!! __('blogr::blogr.translation.azure_metrics_help') !!}
                </p>
            </div>
        @endif
    @endif

    <p class="blogr-usage-note">
        @lang('blogr::blogr.translation.local_counter')
    </p>
</div>
