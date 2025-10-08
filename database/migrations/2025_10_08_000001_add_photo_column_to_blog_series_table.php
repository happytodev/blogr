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
        // Check if column doesn't exist before adding it
        if (!Schema::hasColumn('blog_series', 'photo')) {
            Schema::table('blog_series', function (Blueprint $table) {
                $table->string('photo')->nullable()->after('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('blog_series', 'photo')) {
            Schema::table('blog_series', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
