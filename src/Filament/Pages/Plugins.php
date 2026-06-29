<?php

namespace Happytodev\Blogr\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Happytodev\Blogr\Services\ExtensionRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Plugins extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Plugins';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected string $view = 'blogr::filament.pages.plugins';

    public function getTitle(): string
    {
        return __('Plugins');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Plugins'),
        ];
    }

    public function getExtensionsList(): array
    {
        return app(ExtensionRegistry::class)->getAll();
    }

    /** @return string[] */
    public function getDisabledExtensions(): array
    {
        return app(ExtensionRegistry::class)->getDisabledIds();
    }

    public function toggleExtension(string $id): void
    {
        if ($id === 'blogr-core') {
            Notification::make()
                ->title(__('Core plugin'))
                ->body(__('The core plugin cannot be disabled.'))
                ->warning()
                ->send();

            return;
        }

        $registry = app(ExtensionRegistry::class);

        if ($registry->isEnabled($id)) {
            $registry->disable($id);

            Notification::make()
                ->title(__('Plugin disabled'))
                ->body(__(':name has been disabled.', ['name' => $this->getExtensionName($id)]))
                ->warning()
                ->send();
        } else {
            $registry->enable($id);

            Notification::make()
                ->title(__('Plugin enabled'))
                ->body(__(':name has been enabled.', ['name' => $this->getExtensionName($id)]))
                ->success()
                ->send();
        }
    }

    /**
     * @return array<int, array{name: string, description: string, url: string}>
     */
    public function getCommunityPlugins(): array
    {
        $discovered = Cache::remember('blogr:community-plugins', 86400, function () {
            return $this->fetchCommunityPlugins();
        });

        return $this->filterInstalledPlugins($discovered);
    }

    /**
     * @param  array<int, array{name: string, description: string, url: string}>  $plugins
     * @return array<int, array{name: string, description: string, url: string}>
     */
    protected function filterInstalledPlugins(array $plugins): array
    {
        $installed = app(ExtensionRegistry::class)->getAll();

        return array_values(array_filter($plugins, function (array $plugin) use ($installed): bool {
            $repoName = $this->extractRepoName($plugin['url']);

            foreach ($installed as $extension) {
                // Match by extension ID (e.g. 'blogr-gdpr' from 'happytodev/blogr-gdpr')
                if ($repoName !== null && $extension->getId() === $repoName) {
                    return false;
                }

                // Match by homepage URL
                if ($extension->getHomepage() === $plugin['url']) {
                    return false;
                }
            }

            return true;
        }));
    }

    protected function extractRepoName(string $url): ?string
    {
        // Extract 'blogr-gdpr' from 'https://github.com/happytodev/blogr-gdpr'
        if (preg_match('#/([^/]+?)(?:\.git)?$#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return array<int, array{name: string, description: string, url: string}>
     */
    protected function fetchCommunityPlugins(): array
    {
        // Try local README first (covers development and vendored installs)
        $localPath = __DIR__.'/../../../README.md';

        if (file_exists($localPath)) {
            $content = file_get_contents($localPath);

            if ($content !== false) {
                $plugins = $this->parsePluginTable($content);

                if ($plugins !== []) {
                    return $plugins;
                }
            }
        }

        // Fallback: fetch from GitHub for the latest community listing
        try {
            $response = Http::timeout(5)->get('https://raw.githubusercontent.com/happytodev/blogr/main/README.md');

            if ($response->successful()) {
                return $this->parsePluginTable($response->body());
            }
        } catch (\Throwable) {
            // Silently fail
        }

        return [];
    }

    /**
     * @return array<int, array{name: string, description: string, url: string}>
     */
    protected function parsePluginTable(string $markdown): array
    {
        $plugins = [];
        $lines = explode("\n", $markdown);
        $inSection = false;
        $inTable = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Detect start of ## Plugins section
            if (preg_match('/^##\s+Plugins\s*$/', $trimmed)) {
                $inSection = true;
                $inTable = false;

                continue;
            }

            if (! $inSection) {
                continue;
            }

            // End of section: next heading or horizontal rule
            if (preg_match('/^##/', $trimmed) || preg_match('/^---+$/', $trimmed)) {
                break;
            }

            // Skip blank lines
            if ($trimmed === '') {
                continue;
            }

            // Detect pipe table rows
            if (str_starts_with($trimmed, '|')) {
                // Skip separator row (|---|---|---)
                if (preg_match('/^\|[\s\-:]+\|$/', $trimmed)) {
                    $inTable = true;

                    continue;
                }

                $inTable = true;

                $columns = explode('|', $trimmed);
                $columns = array_map(fn ($c) => trim($c), $columns);
                $columns = array_values(array_filter($columns, fn ($c) => $c !== ''));

                if (count($columns) >= 3) {
                    $name = strip_tags($columns[0]);
                    $description = strip_tags($columns[1]);
                    $url = $this->extractUrl($columns[2]);

                    if ($name !== '' && $url !== '') {
                        $plugins[] = [
                            'name' => html_entity_decode($name),
                            'description' => html_entity_decode($description),
                            'url' => $url,
                        ];
                    }
                }
            } elseif ($inTable) {
                // Table ended by non-pipe content
                break;
            }
        }

        return $plugins;
    }

    protected function extractUrl(string $cell): string
    {
        // Extract URL from markdown link: [text](url)
        if (preg_match('/\[([^\]]+)\]\(([^)]+)\)/', $cell, $matches)) {
            return $matches[2];
        }

        // Fallback: raw URL
        if (preg_match('/https?:\/\/[^\s]+/', $cell, $matches)) {
            return $matches[0];
        }

        return '';
    }

    private function getExtensionName(string $id): string
    {
        $extensions = app(ExtensionRegistry::class)->getAll();

        foreach ($extensions as $ext) {
            if ($ext->getId() === $id) {
                return $ext->getName();
            }
        }

        return $id;
    }
}
