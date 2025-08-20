<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create(config('blogr.tables.prefix', '') . 'categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Créer la catégorie par défaut
        DB::table(config('blogr.tables.prefix', '') . 'categories')->insert([
            'name' => 'General',
            'slug' => 'general',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists(config('blogr.tables.prefix', '') . 'categories');
    }
};