<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Assurez-vous que ceci retourne true
    }

    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mot_de_passe' => [
                'required',
                'string',
                Password::min(8)
                         ->mixedCase()
                         ->numbers()
                         ->symbols(),
            ],
            'mot_de_passe_confirmation' => 'required|same:mot_de_passe',
            'role' => 'required|string|in:ADMIN,BOUTIQUIER,CLIENT',
        ];
    }
}
