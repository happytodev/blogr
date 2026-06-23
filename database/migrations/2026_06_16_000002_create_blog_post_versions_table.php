<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_translation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->string('tldr', 500)->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('photo')->nullable();
            $table->json('categories')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->unique(['blog_post_translation_id', 'version_number'], 'post_version_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_versions');
    }
};
