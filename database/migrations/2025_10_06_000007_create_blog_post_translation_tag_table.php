<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_translation_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_translation_id')->constrained('blog_post_translations')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['blog_post_translation_id', 'tag_id'], 'post_translation_tag_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translation_tag');
    }
};
