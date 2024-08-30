<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class CustomPassword implements Rule
{
    /**
     * Indique si la règle de validation est valide.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return \Illuminate\Support\Facades\Hash::check($value, auth()->user()->password);
    }

    /**
     * Obtenir le message de validation.
     *
     * @return string
     */
    public function message()
    {
        return 'Le mot de passe est incorrect.';
    }
}
