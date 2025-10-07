<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Make translatable fields nullable since they'll be in translations table
            $table->string('title')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->string('slug')->nullable()->change();
            $table->text('tldr')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->text('content')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
            $table->text('tldr')->nullable(false)->change();
        });
    }
};
