<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Vérifier si la colonne 'role' existe
            if (Schema::hasColumn('users', 'role')) {
                // Renommer l'ancienne colonne
                $table->renameColumn('role', 'old_role');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Ajouter la nouvelle colonne role avec l'énumération
            $table->enum('role', array_column(RoleEnum::cases(), 'value'))
                  ->default(RoleEnum::USER->value)
                  ->after('mot_de_passe');
        });

        // Mettre à jour les rôles existants si nécessaire
        if (Schema::hasColumn('users', 'old_role')) {
            DB::table('users')
                ->whereIn('old_role', ['ADMIN', 'BOUTIQUIER', 'CLIENT'])
                ->update([
                    'role' => DB::raw("CASE
                        WHEN old_role = 'ADMIN' THEN '" . RoleEnum::ADMIN->value . "'
                        WHEN old_role = 'BOUTIQUIER' THEN '" . RoleEnum::USER->value . "'
                        WHEN old_role = 'CLIENT' THEN '" . RoleEnum::CLIENT->value . "'
                    END")
                ]);

            // Supprimer l'ancienne colonne
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('old_role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer la nouvelle colonne role
            $table->dropColumn('role');

            // Réajouter l'ancienne colonne role si elle n'existe pas
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['ADMIN', 'BOUTIQUIER', 'CLIENT'])->default('BOUTIQUIER');
            }
        });
    }
};
