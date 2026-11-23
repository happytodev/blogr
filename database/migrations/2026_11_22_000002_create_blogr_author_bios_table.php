<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An author can keep several bios — one per audience, per locale — and a post
 * picks the one it shows (issue #373).
 *
 * `users.bio` stays the default bio per locale: this table only adds the named
 * ones, so an existing site loses nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogr_author_bios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('label');
            $table->text('body')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'locale', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogr_author_bios');
    }
};
