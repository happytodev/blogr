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
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // Global unique slug for routing
            $table->string('template')->default('default'); // Template type (default, landing, contact, etc.)
            $table->longText('blocks')->nullable(); // JSON blocks for page builder
            $table->boolean('is_published')->default(false);
            $table->boolean('is_homepage')->default(false); // Only one homepage allowed
            $table->timestamp('published_at')->nullable();
            $table->string('default_locale', 2)->default('en'); // Default locale for this page
            $table->timestamps();

            // Indexes for performance
            $table->index('slug');
            $table->index('is_published');
            $table->index('is_homepage');
            $table->index('template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
