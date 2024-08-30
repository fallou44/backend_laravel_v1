<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'sometimes|required|string|max:255|unique:promos,code,' . $this->promo->id,
            'pourcentage_reduction' => 'sometimes|required|numeric|min:0|max:100',
            'date_debut' => 'sometimes|required|date',
            'date_fin' => 'sometimes|required|date|after:date_debut',
        ];
    }
}
