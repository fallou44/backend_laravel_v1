<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paiement;
use App\Models\Dette;

class PaiementSeeder extends Seeder
{
    public function run()
    {
        Dette::all()->each(function ($dette) {
            $nombrePaiements = rand(1, 3);
            Paiement::factory()->count($nombrePaiements)->create([
                'dette_id' => $dette->id,
            ]);
        });
    }
}
