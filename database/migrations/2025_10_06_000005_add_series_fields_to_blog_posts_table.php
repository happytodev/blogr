<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('blog_series_id')->nullable()->after('id')->constrained('blog_series')->onDelete('set null');
            $table->integer('series_position')->nullable()->after('blog_series_id');
            $table->string('default_locale', 10)->default('en')->after('series_position');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['blog_series_id']);
            $table->dropColumn(['blog_series_id', 'series_position', 'default_locale']);
        });
    }
};
