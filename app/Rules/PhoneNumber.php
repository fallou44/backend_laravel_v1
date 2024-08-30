<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
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
        // Enlever les espaces et les caractères non numériques
        $value = preg_replace('/\D/', '', $value);

        // Vérifier l'indicatif international
        if (strpos($value, '+221') === 0 || strpos($value, '221') === 0) {
            $value = substr($value, 4); // Retirer l'indicatif +221
        }

        // Vérifier la longueur du numéro sans l'indicatif
        if (strlen($value) !== 9) {
            return false;
        }

        // Vérifier les préfixes valides
        $prefixes = ['77', '78', '76', '70', '75'];
        $prefix = substr($value, 0, 2);

        return in_array($prefix, $prefixes);
    }

    /**
     * Obtenir le message de validation.
     *
     * @return string
     */
    public function message()
    {
        return 'Le numéro de téléphone n\'est pas valide. Assurez-vous qu\'il commence par un indicatif correct (+221) et un préfixe valide (77, 78, 76, 70, 75).';
    }
}
