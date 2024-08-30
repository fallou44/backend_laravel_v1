<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Http\Resources\PromoResource;
use App\Http\Resources\PromoCollection;

class PromoController extends Controller
{
    public function index()
    {
        return new PromoCollection(Promo::paginate());
    }

    public function store(StorePromoRequest $request)
    {
        $promo = Promo::create($request->validated());
        return new PromoResource($promo);
    }

    public function show(Promo $promo)
    {
        return new PromoResource($promo);
    }

    public function update(UpdatePromoRequest $request, Promo $promo)
    {
        $promo->update($request->validated());
        return new PromoResource($promo);
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return response()->json(null, 204);
    }
}
