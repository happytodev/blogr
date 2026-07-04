<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Contracts\ExportableExtension;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExtensionRegistry
{
    /** @var array<string, BlogrExtension> */
    protected array $extensions = [];

    protected array $disabledIds = [];

    protected bool $loadedFromDb = false;

    public function register(BlogrExtension $extension): void
    {
        $this->extensions[$extension->getId()] = $extension;
        $this->ensureStateInDb($extension->getId());
    }

    /** @return array<string, BlogrExtension> */
    public function getAll(): array
    {
        return $this->extensions;
    }

    /** @return array<string, ExportableExtension> */
    public function getExportableExtensions(): array
    {
        return array_filter(
            $this->extensions,
            fn (BlogrExtension $ext) => $ext instanceof ExportableExtension
        );
    }

    public function get(string $id): ?BlogrExtension
    {
        return $this->extensions[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->extensions[$id]);
    }

    public function count(): int
    {
        return count($this->extensions);
    }

    public function enable(string $id): void
    {
        unset($this->disabledIds[$id]);

        if ($this->hasDbTable()) {
            DB::table('blogr_extension_states')->updateOrInsert(
                ['extension_id' => $id],
                ['disabled_at' => null, 'updated_at' => now()]
            );
            Cache::forget('blogr:extension-states');
        }
    }

    public function disable(string $id): void
    {
        $this->disabledIds[$id] = true;

        if ($this->hasDbTable()) {
            DB::table('blogr_extension_states')->updateOrInsert(
                ['extension_id' => $id],
                ['disabled_at' => now(), 'updated_at' => now()]
            );
            Cache::forget('blogr:extension-states');
        }
    }

    public function isEnabled(string $id): bool
    {
        $this->loadStatesIfNeeded();

        return ! isset($this->disabledIds[$id]);
    }

    /** @return array<string, BlogrExtension> */
    public function getEnabled(): array
    {
        return array_filter(
            $this->extensions,
            fn (BlogrExtension $ext) => $this->isEnabled($ext->getId())
        );
    }

    /** @return string[] */
    public function getDisabledIds(): array
    {
        $this->loadStatesIfNeeded();

        return array_keys($this->disabledIds);
    }

    protected function loadStatesIfNeeded(): void
    {
        if ($this->loadedFromDb) {
            return;
        }

        if (! $this->hasDbTable()) {
            return;
        }

        $this->disabledIds = Cache::remember('blogr:extension-states', 3600, function () {
            return DB::table('blogr_extension_states')
                ->whereNotNull('disabled_at')
                ->pluck('extension_id')
                ->flip()
                ->map(fn () => true)
                ->toArray();
        });

        $this->loadedFromDb = true;
    }

    protected function hasDbTable(): bool
    {
        try {
            return Schema::hasTable('blogr_extension_states');
        } catch (\Exception) {
            return false;
        }
    }

    protected function ensureStateInDb(string $id): void
    {
        if (! $this->hasDbTable()) {
            return;
        }

        DB::table('blogr_extension_states')->upsert(
            [
                'extension_id' => $id,
                'disabled_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'extension_id',
            ['updated_at']
        );
    }
}
