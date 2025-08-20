<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('blogr.tables.prefix', '') . 'blog_post_tag', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->constrained(config('blogr.tables.prefix', '') . 'blog_posts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained(config('blogr.tables.prefix', '') . 'tags')->onDelete('cascade');
            $table->primary(['blog_post_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('blogr.tables.prefix', '') . 'blog_post_tag');
    }
};