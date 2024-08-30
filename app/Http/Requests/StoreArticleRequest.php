<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'libele' => 'required|string|max:255|unique:articles,libele',
            'prix_unitaire' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:0',
            'prix_details' => 'required|numeric|min:0',
            'categorie_id' => 'required|exists:categories,id',
            'promo_id' => 'nullable|exists:promos,id',
        ];
    }
}
