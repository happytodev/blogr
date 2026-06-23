<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_translation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->json('blocks')->nullable();
            $table->json('categories')->nullable();
            $table->timestamps();

            $table->unique(['cms_page_translation_id', 'version_number'], 'cms_version_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_page_versions');
    }
};
