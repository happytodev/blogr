<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_series_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_series_id')->constrained('blog_series')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['blog_series_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_series_translations');
    }
};
