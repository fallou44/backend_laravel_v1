<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'libele' => 'sometimes|required|string|max:255',
            'prix_unitaire' => 'sometimes|required|numeric|min:0',
            'quantite' => 'sometimes|required|integer|min:0',
            'prix_details' => 'sometimes|required|numeric|min:0',
            'categorie_id' => 'sometimes|required|exists:categories,id',
            'promo_id' => 'nullable|exists:promos,id',
        ];
    }
}
