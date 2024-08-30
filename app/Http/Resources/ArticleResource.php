<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ArticleResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'libele' => $this->libele,
            'prix_unitaire' => $this->prix_unitaire,
            'quantite' => $this->quantite,
            'prix_details' => $this->prix_details,
            'categorie' => $this->categorie->nom,
            'promo' => $this->promo ? $this->promo->code : null,

        ];
    }
}

