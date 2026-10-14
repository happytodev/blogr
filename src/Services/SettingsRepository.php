<?php

namespace Happytodev\Blogr\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Stores the settings an admin saves from the Settings page.
 *
 * Why a table instead of the config file: each deployment builds a fresh
 * release directory from the repository, so a value written to
 * `config/blogr.php` on the server is replaced by the next deploy — and
 * `config:clear` on its own is not the problem, the deploy is. Values kept
 * here are applied on top of the file at boot, which makes them durable while
 * the committed file keeps playing the role of the defaults.
 *
 * An empty table means "no override": the config file wins, exactly as before,
 * so an existing site keeps behaving identically until something is saved.
 */
class SettingsRepository
{
    public const TABLE = 'blogr_settings';

    public const CACHE_KEY = 'blogr:settings-overrides';

    /**
     * Memoised on the instance, not statically: the repository is a singleton for
     * one request, and a static would leak the overrides of one test into the next
     * one in the same process.
     */
    /** @var array<string, mixed>|null */
    protected ?array $memo = null;

    /**
     * Every override, as a flat list of dotted config keys (e.g.
     * `ui.theme.primary_color`) mapped to their decoded value.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->memo !== null) {
            return $this->memo;
        }

        try {
            return $this->memo = Cache::rememberForever(self::CACHE_KEY, fn () => $this->readFromDatabase());
        } catch (Throwable) {
            // An unusable cache store must never take the site down: read the
            // table directly rather than fail.
            return $this->memo = $this->readFromDatabase();
        }
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function hasTable(): bool
    {
        try {
            return Schema::hasTable(self::TABLE);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Apply every override on top of the configuration loaded from the file.
     *
     * `all()` is memoised and cached, so a request that has nothing to override
     * costs one cache read and no schema lookup — the migration clears that
     * cache, which is what makes the first request after migrating read the
     * (still empty) table instead of a stale "no table" answer.
     */
    public function applyToConfig(): void
    {
        foreach ($this->all() as $key => $value) {
            config()->set("blogr.{$key}", $value);
        }
    }

    /**
     * Store the given dotted keys as overrides.
     *
     * A null is not a value here: it means "unset", so it is dropped rather than
     * stored — an override is what an admin asked for, and a null would make the
     * file's value unreachable for a field nobody filled.
     *
     * @param  array<string, mixed>  $values
     */
    public function put(array $values): void
    {
        $values = array_filter($values, fn ($value) => $value !== null);

        if ($values === [] || ! $this->hasTable()) {
            return;
        }

        $now = now();

        DB::transaction(function () use ($values, $now) {
            foreach ($values as $key => $value) {
                DB::table(self::TABLE)->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        });

        $this->flush();
    }

    /**
     * Drop every override, so the values from the config file apply again.
     *
     * @return int number of deleted overrides
     */
    public function forgetAll(): int
    {
        if (! $this->hasTable()) {
            return 0;
        }

        $deleted = DB::table(self::TABLE)->delete();

        $this->flush();

        return $deleted;
    }

    /** Forget the memoised list and the cache entry after a write. */
    public function flush(): void
    {
        $this->memo = null;

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable) {
        }
    }

    /** @return array<string, mixed> */
    protected function readFromDatabase(): array
    {
        if (! $this->hasTable()) {
            return [];
        }

        return DB::table(self::TABLE)
            ->pluck('value', 'key')
            ->map(function ($value): mixed {
                return is_string($value) ? json_decode($value, true) : null;
            })
            ->all();
    }
}
