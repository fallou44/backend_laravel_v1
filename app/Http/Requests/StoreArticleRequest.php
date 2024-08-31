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
            'description' => 'required|string',
            'quantite' => 'required|integer|min:0',
            'prix_unitaire' => 'required|numeric|min:0',
            'prix_details' => 'required|numeric|min:0',
            'categorie_id' => 'required|exists:categories,id',
        ];
    }

    public function messages()
    {
        return [
            'libele.unique' => 'Le libellé de l\'article doit être unique.',
            'libele.required' => 'Le libellé de l\'article est requis.',
            'libele.string' => 'Le libellé de l\'article doit être une chaîne de caractères.',
            'libele.max' => 'Le libellé de l\'article ne peut pas dépasser 255 caractères.',
            'description.required' => 'La description est requise.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'quantite.required' => 'La quantité est requise.',
            'quantite.integer' => 'La quantité doit être un entier.',
            'quantite.min' => 'La quantité ne peut pas être négative.',
            'prix_unitaire.required' => 'Le prix unitaire est requis.',
            'prix_unitaire.numeric' => 'Le prix unitaire doit être un nombre.',
            'prix_unitaire.min' => 'Le prix unitaire ne peut pas être négatif.',
            'prix_details.required' => 'Le prix des détails est requis.',
            'prix_details.numeric' => 'Le prix des détails doit être un nombre.',
            'prix_details.min' => 'Le prix des détails ne peut pas être négatif.',
            'categorie_id.required' => 'La catégorie est requise.',
            'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
        ];
    }
}
