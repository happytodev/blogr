<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add blocks column to cms_page_translations
        Schema::table('cms_page_translations', function (Blueprint $table) {
            $table->longText('blocks')->nullable()->after('content');
        });

        // Remove blocks column from cms_pages (we'll do this in a separate step after data migration)
        // For now, we'll keep both columns to allow gradual migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_page_translations', function (Blueprint $table) {
            $table->dropColumn('blocks');
        });
    }
};
