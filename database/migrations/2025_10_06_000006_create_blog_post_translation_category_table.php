<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_translation_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_translation_id')->constrained('blog_post_translations')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['blog_post_translation_id', 'category_id'], 'post_translation_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translation_category');
    }
};
