<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable(); // Nullable for flexibility
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->integer('reading_time')->nullable();
            $table->timestamps();

            // Unique constraint: one translation per locale per post
            $table->unique(['blog_post_id', 'locale']);
            
            // Unique constraint: slug must be unique globally (across all locales)
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translations');
    }
};
