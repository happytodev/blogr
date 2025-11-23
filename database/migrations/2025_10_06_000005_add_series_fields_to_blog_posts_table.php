<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Add columns without foreign key constraint initially (SQLite compatibility)
            // Foreign key will be added via another migration if needed
            if (!Schema::hasColumn('blog_posts', 'blog_series_id')) {
                $table->unsignedBigInteger('blog_series_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('blog_posts', 'series_position')) {
                $table->integer('series_position')->nullable()->after('blog_series_id');
            }
            if (!Schema::hasColumn('blog_posts', 'default_locale')) {
                $table->string('default_locale', 10)->default('en')->after('series_position');
            }
        });

        // Add foreign key separately for databases that support it better
        if (Schema::hasColumn('blog_posts', 'blog_series_id') && Schema::hasTable('blog_series')) {
            try {
                Schema::table('blog_posts', function (Blueprint $table) {
                    // Check if foreign key doesn't already exist before adding
                    if (!Schema::connection(null)->getConnection()->selectOne(
                        "SELECT 1 FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='blog_posts' AND COLUMN_NAME='blog_series_id' AND REFERENCED_TABLE_NAME='blog_series' LIMIT 1"
                    )) {
                        $table->foreign('blog_series_id')
                            ->references('id')
                            ->on('blog_series')
                            ->onDelete('set null');
                    }
                });
            } catch (\Exception $e) {
                // Foreign key addition failed (likely SQLite) - that's ok, columns are added
            }
        }
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Try to drop foreign key if it exists (will fail silently on SQLite if it doesn't exist)
            try {
                $table->dropForeign(['blog_series_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            $table->dropColumn(['blog_series_id', 'series_position', 'default_locale']);
        });
    }
};
