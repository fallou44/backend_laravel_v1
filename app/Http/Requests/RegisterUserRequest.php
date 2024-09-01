<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Assurez-vous que ceci retourne true
    }

    /**
     * Obtenez les règles de validation qui s'appliquent à la requête.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'mot_de_passe' => [
                'required',
                'string',
                Password::min(5)
                         ->mixedCase()
                         ->numbers()
                         ->symbols(),
            ],
            'mot_de_passe_confirmation' => 'required|same:mot_de_passe',
            'role' => 'required|string|in:CLIENT,TAILLEUR,VENDEUR,ADMIN',
            'clientId' => 'required|exists:clients,id', // Assurez-vous que clientId existe dans la table clients
        ];
    }

    /**
     * Obtenez les messages de validation personnalisés.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'mot_de_passe_confirmation.same' => 'Les mots de passe ne correspondent pas.',
            'clientId.exists' => 'Le client spécifié n\'existe pas.',
        ];
    }
}
