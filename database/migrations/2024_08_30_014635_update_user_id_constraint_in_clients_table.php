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
        Schema::table('clients', function (Blueprint $table) {
            // Vérifier si la contrainte existe déjà
            if (Schema::hasColumn('clients', 'user_id')) {
                // Supprimer la contrainte de clé étrangère si elle existe
                $table->dropForeign(['user_id']);
                $table->dropUnique(['user_id']);

                // Ajouter la contrainte de clé étrangère avec 'set null'
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->unique('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Supprimer les contraintes existantes
            $table->dropUnique(['user_id']);
            $table->dropForeign(['user_id']);

            // Recréer la contrainte de clé étrangère avec 'cascade'
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
