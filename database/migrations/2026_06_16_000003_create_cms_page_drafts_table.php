<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_page_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_translation_id')->constrained()->cascadeOnDelete()->unique();
            $table->json('draft_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_page_drafts');
    }
};
