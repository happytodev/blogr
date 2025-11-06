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
        // Vérifier si la table users existe avant d'ajouter les colonnes
        if (!Schema::hasTable('users')) {
            return;
        }
        
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->json('bio')->nullable()->after('avatar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vérifier si la table users existe avant de tenter de supprimer les colonnes
        if (!Schema::hasTable('users')) {
            return;
        }
        
        Schema::table('users', function (Blueprint $table) {
            // Pour SQLite : supprimer l'index unique AVANT la colonne
            if (Schema::hasColumn('users', 'slug')) {
                $table->dropUnique(['slug']);
            }
        });
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            if (Schema::hasColumn('users', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};
