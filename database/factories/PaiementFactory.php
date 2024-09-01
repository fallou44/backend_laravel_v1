<?php

namespace Database\Factories;

use App\Models\Paiement;
use App\Models\Dette;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaiementFactory extends Factory
{
    protected $model = Paiement::class;

    public function definition()
    {
        return [
            'dette_id' => Dette::factory(),
            'montant' => $this->faker->randomFloat(2, 10, 1000),
            'date_paiement' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'mode_paiement' => $this->faker->randomElement(['espèces', 'carte', 'virement']),
            'commentaire' => $this->faker->optional()->sentence,
        ];
    }
}
