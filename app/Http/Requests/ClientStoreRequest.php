<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PhoneNumber;
use App\Traits\ApiResponse;
use App\Enums\StatusEnum;
use Illuminate\Validation\Rules\Password;

class ClientStoreRequest extends FormRequest
{
    use ApiResponse;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'surnom' => 'required|string|max:255|unique:clients,surnom',
            'telephone' => ['required', 'string', new PhoneNumber, 'unique:clients,telephone'],
            'user' => 'sometimes|array',
            'user.prenom' => 'required_with:user|string|max:255',
            'user.nom' => 'required_with:user|string|max:255',
            'user.email' => 'required_with:user|string|email|max:255|unique:users,email',
            'user.mot_de_passe' => ['required_with:user', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'user.mot_de_passe_confirmation' => 'required_with:user.mot_de_passe|same:user.mot_de_passe',
            'user.role' => 'required_with:user|string|in:CLIENT',
        ];
    }

    public function messages()
    {
        return [
            'surnom.required' => 'Le surnom est obligatoire.',
            'surnom.unique' => 'Ce surnom est déjà utilisé.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'user.prenom.required_with' => 'Le prénom de l\'utilisateur est obligatoire.',
            'user.nom.required_with' => 'Le nom de l\'utilisateur est obligatoire.',
            'user.email.required_with' => 'L\'email de l\'utilisateur est obligatoire.',
            'user.email.unique' => 'Cet email est déjà utilisé.',
            'user.mot_de_passe.required_with' => 'Le mot de passe de l\'utilisateur est obligatoire.',
            'user.mot_de_passe_confirmation.required_with' => 'La confirmation du mot de passe est obligatoire.',
            'user.mot_de_passe_confirmation.same' => 'La confirmation du mot de passe ne correspond pas.',
            'user.role.required_with' => 'Le rôle de l\'utilisateur est obligatoire.',
            'user.role.in' => 'Le rôle de l\'utilisateur doit être CLIENT.',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        throw new \Illuminate\Validation\ValidationException($validator, $this->sendResponse(StatusEnum::ERROR, $errors->first(), 422));
    }
}
