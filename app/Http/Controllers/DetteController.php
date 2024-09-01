<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use Illuminate\Http\Request;

class DetteController extends Controller
{
    public function index()
    {
        $dettes = Dette::with(['client', 'articles'])->paginate(15);
        return response()->json($dettes);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'montant_total' => 'required|numeric',
            'date_echeance' => 'required|date',
            'statut' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'articles' => 'required|array',
            'articles.*.id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
            'articles.*.prix_unitaire' => 'required|numeric',
        ]);

        $dette = Dette::create($validatedData);

        foreach ($validatedData['articles'] as $article) {
            $dette->articles()->attach($article['id'], [
                'quantite' => $article['quantite'],
                'prix_unitaire' => $article['prix_unitaire'],
            ]);
        }

        return response()->json($dette->load('articles'), 201);
    }

    public function show(Dette $dette)
    {
        return response()->json($dette->load(['client', 'articles', 'paiements']));
    }

    public function update(Request $request, Dette $dette)
    {
        $validatedData = $request->validate([
            'montant_total' => 'numeric',
            'date_echeance' => 'date',
            'statut' => 'string',
        ]);

        $dette->update($validatedData);

        return response()->json($dette);
    }

    public function destroy(Dette $dette)
    {
        $dette->delete();
        return response()->json(null, 204);
    }
}
