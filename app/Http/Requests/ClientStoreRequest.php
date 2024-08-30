<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PhoneNumber;
use App\Traits\ApiResponse;
use App\Enums\StatusEnum;

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
            'surnom' => 'required|string|max:255',
            'telephone' => ['required', 'string', new PhoneNumber, 'unique:clients,telephone'],
            'adresse' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
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

    public function failedValidation($validator)
    {
        throw new \Illuminate\Validation\ValidationException($this->sendResponse(StatusEnum::ERROR, $validator->errors()->first(), 422));
    }
}



// {
//     "surnom": "ASTAR",
//     "telephone": "778765432",
//     "adresse": "PLATEAU",
//     "user": {
//       "prenom": "Fatoumata",
//       "nom": "Sarr",
//       "email": "sarr@gamil.com",
//       "mot_de_passe": "Passer123@",
//       "role": "CLIENT"
//     }
//   }
