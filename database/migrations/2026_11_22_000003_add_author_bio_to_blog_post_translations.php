<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The bio a translation shows, chosen among the author's bios (issue #373).
 * Nullable: without a choice, the default bio of the locale applies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->foreignId('author_bio_id')
                ->nullable()
                ->after('locale')
                ->constrained('blogr_author_bios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_bio_id');
        });
    }
};
