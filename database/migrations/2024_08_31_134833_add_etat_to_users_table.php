<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter la colonne 'etat' qui sera un booléen pour indiquer si le compte est bloqué ou non
            $table->boolean('etat')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer la colonne 'etat' si la migration est annulée
            $table->dropColumn('etat');
        });
    }
};
