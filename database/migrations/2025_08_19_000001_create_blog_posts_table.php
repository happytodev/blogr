<?php

use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('blogr.tables.prefix', '') . 'blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Nullable for translation system
            $table->string('photo')->nullable();
            $table->text('content')->nullable(); // Nullable for translation system
            $table->string('slug')->unique()->nullable(); // Nullable for translation system
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('tldr')->nullable();
            $table->foreignId('category_id')
                ->constrained(config('blogr.tables.prefix', '') . 'categories')
                ->default(function () {
                    return Category::where('is_default', true)->first()->id;
                });
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('blogr.tables.prefix', '') . 'blog_posts');
    }
};
