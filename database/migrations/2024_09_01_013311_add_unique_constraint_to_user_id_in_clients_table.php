<?php
// database/migrations/2024_08_30_014635_update_user_id_constraint_in_clients_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Essaye de supprimer une contrainte unique qui n'existe pas
            $table->dropUnique(['user_id']); // Assurez-vous que cela est correct
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Ajoute la contrainte unique sur user_id
            $table->unique('user_id');
        });
    }
};
