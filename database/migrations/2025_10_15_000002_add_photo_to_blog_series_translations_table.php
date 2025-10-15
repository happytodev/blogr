<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_series_translations', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('blog_series_translations', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
