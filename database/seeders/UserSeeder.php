<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Créer un admin et un boutiquier
        User::factory()->create(['role' => 'ADMIN']);
        User::factory()->create(['role' => 'BOUTIQUIER']);

        // Créer 3 clients avec un user associé
        Client::factory(3)->create()->each(function ($client) {
            $user = User::factory()->create();
            $client->user_id = $user->id;
            $client->save();
        });
    }
}
