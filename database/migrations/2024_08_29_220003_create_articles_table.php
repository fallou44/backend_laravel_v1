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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('libele');
            $table->decimal('prix_unitaire', 10, 2);
            $table->integer('quantite');
            $table->decimal('prix_details', 10, 2);
            $table->foreignId('categorie_id')->constrained('categories');
            $table->foreignId('promo_id')->nullable()->constrained('promos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
