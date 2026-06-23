<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_translation_id')->nullable()->constrained()->cascadeOnDelete()->unique();
            $table->unsignedBigInteger('blog_post_id')->nullable()->unique();
            $table->json('draft_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_drafts');
    }
};
