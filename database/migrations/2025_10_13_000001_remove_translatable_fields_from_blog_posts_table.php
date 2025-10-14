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
        Schema::table('blog_posts', function (Blueprint $table) {
            // Drop indexes first
            $table->dropUnique(['slug']); // Drop blog_posts_slug_unique index
            
            // Now remove translatable fields (now only in blog_post_translations)
            $table->dropColumn([
                'title',
                'slug',
                'content',
                'tldr',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Restore fields for rollback (though this is destructive)
            $table->string('title')->nullable()->after('blog_series_id');
            $table->string('slug')->unique()->nullable()->after('title');
            $table->longText('content')->nullable()->after('slug');
            $table->text('tldr')->nullable()->after('content');
            $table->string('meta_title', 60)->nullable()->after('tldr');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });
    }
};

