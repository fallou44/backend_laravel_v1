<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Ajoute une contrainte unique sur 'user_id'
            $table->unique('user_id');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Supprime la contrainte unique sur 'user_id'
            $table->dropUnique(['user_id']);
        });
    }
};
