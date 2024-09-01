<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserIdConstraintInClientsTable extends Migration
{
    public function up()
    {
        // Code pour ajouter ou modifier des colonnes ou des contraintes
        Schema::table('clients', function (Blueprint $table) {
            $table->unique('user_id', 'clients_user_id_unique'); // Assurez-vous que le nom est correct
        });
    }

    public function down()
    {
        // Code pour supprimer ou restaurer des colonnes ou des contraintes
        Schema::table('clients', function (Blueprint $table) {
            // Vérifiez si la contrainte existe avant d'essayer de la supprimer
            if (Schema::hasColumn('clients', 'user_id')) {
                $table->dropUnique('clients_user_id_unique'); // Suppression de la contrainte
            }
        });
    }
}
