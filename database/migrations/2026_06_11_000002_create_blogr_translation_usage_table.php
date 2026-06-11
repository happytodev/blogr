<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogr_translation_usage', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->integer('char_count')->default(0);
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->timestamps();

            $table->unique(['provider', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogr_translation_usage');
    }
};
