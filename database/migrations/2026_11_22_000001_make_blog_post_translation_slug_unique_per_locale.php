<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A post slug was unique across every locale, although the route that serves it
 * carries the locale (issue #365). Two translations of the same article could
 * not share a slug, and the second one was refused by the admin.
 *
 * Categories, tags and CMS pages were already unique per locale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->dropUnique('blog_post_translations_slug_unique');
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->dropUnique('blog_post_translations_locale_slug_unique');
            $table->unique('slug');
        });
    }
};
