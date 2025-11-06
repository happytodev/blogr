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
        Schema::table(config('blogr.tables.prefix', '') . 'blog_posts', function (Blueprint $table) {
            $table->boolean('display_toc')->nullable()->after('default_locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('blogr.tables.prefix', '') . 'blog_posts', function (Blueprint $table) {
            $table->dropColumn('display_toc');
        });
    }
};
