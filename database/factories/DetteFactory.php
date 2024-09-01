<?php

namespace Database\Factories;

use App\Models\Dette;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetteFactory extends Factory
{
    protected $model = Dette::class;

    public function definition()
    {
        return [
            'montant_total' => $this->faker->randomFloat(2, 100, 10000),
            'date_echeance' => $this->faker->dateTimeBetween('now', '+1 year'),
            'statut' => $this->faker->randomElement(['en_cours', 'payee', 'en_retard']),
            'client_id' => Client::factory(),
        ];
    }
}
