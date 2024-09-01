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
        Schema::table('clients', function (Blueprint $table) {
            // Ajoute une contrainte unique sur 'user_id' uniquement si elle n'existe pas
            if (!Schema::hasColumn('clients', 'user_id')) {
                $table->unique('user_id', 'clients_user_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Supprime la contrainte unique sur 'user_id' si elle existe
            if (Schema::hasColumn('clients', 'user_id')) {
                $table->dropUnique('clients_user_id_unique');
            }
        });
    }
};
