<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Dette;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function index()
    {
        $paiements = Paiement::with('dette')->paginate(15);
        return response()->json($paiements);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dette_id' => 'required|exists:dettes,id',
            'montant' => 'required|numeric',
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|string',
            'commentaire' => 'nullable|string',
        ]);

        $paiement = Paiement::create($validatedData);

        $dette = Dette::find($validatedData['dette_id']);
        if ($dette->getMontantPayeAttribute() >= $dette->montant_total) {
            $dette->update(['statut' => 'payee']);
        }

        return response()->json($paiement, 201);
    }

    public function show(Paiement $paiement)
    {
        return response()->json($paiement->load('dette'));
    }

    public function update(Request $request, Paiement $paiement)
    {
        $validatedData = $request->validate([
            'montant' => 'numeric',
            'date_paiement' => 'date',
            'mode_paiement' => 'string',
            'commentaire' => 'nullable|string',
        ]);

        $paiement->update($validatedData);

        return response()->json($paiement);
    }

    public function destroy(Paiement $paiement)
    {
        $paiement->delete();
        return response()->json(null, 204);
    }
}
