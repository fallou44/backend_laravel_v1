<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Http\Requests\StoreCategorieRequest;
use App\Http\Requests\UpdateCategorieRequest;
use App\Http\Resources\CategorieResource;
use App\Http\Resources\CategorieCollection;

class CategorieController extends Controller
{
    public function index()
    {
        return new CategorieCollection(Categorie::paginate());
    }

    public function store(StoreCategorieRequest $request)
    {
        $categorie = Categorie::create($request->validated());
        return new CategorieResource($categorie);
    }

    public function show(Categorie $categorie)
    {
        return new CategorieResource($categorie);
    }

    public function update(UpdateCategorieRequest $request, Categorie $categorie)
    {
        $categorie->update($request->validated());
        return new CategorieResource($categorie);
    }

    public function destroy(Categorie $categorie)
    {
        $categorie->delete();
        return response()->json(null, 204);
    }
}
