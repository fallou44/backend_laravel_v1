<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'articles' => 'required|array|min:1',
            'articles.*.id' => 'required|integer|min:1', // Ajout de min:1 pour garantir un entier positif
            'articles.*.quantite' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value < 0) {
                        $fail('La quantité ne peut pas être négative.');
                    }
                },
            ],
        ];
    }




    public function messages()
    {
        return [
            'articles.required' => 'Au moins un article doit être fourni.',
            'articles.array' => 'Les articles doivent être fournis sous forme de tableau.',
            'articles.min' => 'Au moins un article doit être fourni.',
            'articles.*.id.required' => 'L\'ID de l\'article est requis.',
            'articles.*.id.integer' => 'L\'ID de l\'article doit être un nombre entier.',
            'articles.*.id.min' => 'L\'ID de l\'article doit être un nombre entier positif.',
            'articles.*.quantite.required' => 'La quantité est requise pour chaque article.',
            'articles.*.quantite.integer' => 'La quantité doit être un nombre entier.',
        ];
    }


}
