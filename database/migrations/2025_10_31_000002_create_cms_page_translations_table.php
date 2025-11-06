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
        Schema::create('cms_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cms_page_id')->constrained('cms_pages')->onDelete('cascade');
            $table->string('locale', 2); // en, fr, de, es, etc.
            $table->string('slug'); // Locale-specific slug for routing
            $table->string('title');
            $table->longText('content')->nullable(); // Markdown content for default template
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->timestamps();

            // Unique constraints
            $table->unique(['cms_page_id', 'locale']); // One translation per locale per page
            $table->unique(['locale', 'slug']); // Slug unique within each locale

            // Indexes for performance
            $table->index('locale');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_page_translations');
    }
};
