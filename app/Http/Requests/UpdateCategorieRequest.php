<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategorieRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nom' => 'sometimes|required|string|max:255|unique:categories,nom,' . $this->categorie->id,
            'description' => 'nullable|string',
        ];
    }
}
