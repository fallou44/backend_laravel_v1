<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\PhoneNumber;

class ClientUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'surnom' => 'sometimes|required|string|max:255',
            'telephone' => [
                'sometimes',
                'required',
                'string',
                new PhoneNumber,
                Rule::unique('clients')->ignore($this->client),
            ],
            'adresse' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|nullable|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'surnom.required' => 'Le surnom est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas.',
        ];
    }
}
