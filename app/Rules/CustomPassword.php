<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustomPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $messages = [
            'required' => 'Le champ :attribute est obligatoire.',
            'min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'password' => 'Le mot de passe doit contenir au moins un chiffre, une lettre, et un caractère spécial.',
        ];

        $validator = Validator::make([$attribute => $value], [
            $attribute => ['required', Password::min(8)->letters()->mixedCase()->numbers()->uncompromised()],
        ], $messages);

        if (!$validator->passes()) {
            $fail($validator->errors()->first());
        }
    }
}
