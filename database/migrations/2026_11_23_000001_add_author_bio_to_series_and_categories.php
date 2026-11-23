<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A series can carry a bio, so its posts do not each have to pick one (#376).
 *
 * Per translation: the bio is written in one language, and so is the series page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_series_translations', function (Blueprint $table) {
            $table->foreignId('author_bio_id')
                ->nullable()
                ->after('locale')
                ->constrained('blogr_author_bios')
                ->nullOnDelete();
        });

        Schema::table('category_translations', function (Blueprint $table) {
            $table->foreignId('author_bio_id')
                ->nullable()
                ->after('locale')
                ->constrained('blogr_author_bios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blog_series_translations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_bio_id');
        });

        Schema::table('category_translations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_bio_id');
        });
    }
};
