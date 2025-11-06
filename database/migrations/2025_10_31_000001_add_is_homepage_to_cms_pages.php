<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add column if table exists (CMS tables are optional)
        if (Schema::hasTable('cms_pages')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                $table->boolean('is_homepage')->default(false)->after('is_published');
                $table->index('is_homepage');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cms_pages')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                $table->dropIndex(['is_homepage']);
                $table->dropColumn('is_homepage');
            });
        }
    }
};
