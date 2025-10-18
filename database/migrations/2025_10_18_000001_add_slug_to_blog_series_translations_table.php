<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_series_translations', function (Blueprint $table) {
            $table->string('slug')->after('locale')->nullable();
            $table->unique(['blog_series_id', 'locale', 'slug'], 'series_translation_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('blog_series_translations', function (Blueprint $table) {
            $table->dropUnique('series_translation_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
