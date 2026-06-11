<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_page_translations', function (Blueprint $table) {
            $table->boolean('is_complete')->default(false)->after('meta_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('cms_page_translations', function (Blueprint $table) {
            $table->dropColumn('is_complete');
        });
    }
};
