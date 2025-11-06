<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip for SQLite - columns are already created as nullable in the initial migration
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // MySQL/PostgreSQL: Make translatable fields nullable
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->text('content')->nullable()->change();
            $table->string('slug')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Skip for SQLite - columns were created as nullable in the initial migration
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->text('content')->nullable(false)->change();
            $table->string('slug')->nullable(false)->change();
            
            // Note: Don't touch tldr and SEO fields as they remain nullable
        });
    }
};
