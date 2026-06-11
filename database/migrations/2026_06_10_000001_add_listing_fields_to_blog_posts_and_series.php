<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->boolean('is_listed')->default(true)->after('is_published');
        });

        Schema::table('blog_series', function (Blueprint $table) {
            $table->boolean('show_on_index')->default(true)->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('is_listed');
        });

        Schema::table('blog_series', function (Blueprint $table) {
            $table->dropColumn('show_on_index');
        });
    }
};
