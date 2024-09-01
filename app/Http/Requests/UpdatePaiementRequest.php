<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaiementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'montant' => 'sometimes|numeric',
            'date_paiement' => 'sometimes|date',
            'mode_paiement' => 'sometimes|string',
            'commentaire' => 'nullable|string',
        ];
    }
}
