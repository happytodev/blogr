<?php

use Happytodev\Blogr\Services\SettingsRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(SettingsRepository::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            // Every value is stored as JSON: booleans, integers, null and arrays
            // all survive a round trip, which a text column alone would not.
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 2.6.0 cached the overrides under this key; the list is read directly
        // now, so drop anything left behind.
        try {
            Cache::forget(SettingsRepository::LEGACY_CACHE_KEY);
        } catch (Throwable) {
            // No usable cache store: nothing was cached either.
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(SettingsRepository::TABLE);

        try {
            Cache::forget(SettingsRepository::LEGACY_CACHE_KEY);
        } catch (Throwable) {
        }
    }
};
