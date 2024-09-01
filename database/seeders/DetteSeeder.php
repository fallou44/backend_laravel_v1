<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dette;
use App\Models\Article;

class DetteSeeder extends Seeder
{
    public function run()
    {
        Dette::factory()->count(50)->create()->each(function ($dette) {
            $articles = Article::inRandomOrder()->limit(rand(1, 5))->get();
            foreach ($articles as $article) {
                $dette->articles()->attach($article->id, [
                    'quantite' => rand(1, 10),
                    'prix_unitaire' => $article->prix_unitaire,
                ]);
            }
        });
    }
}
