<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;

class PaiementPolicy
{
    public function viewAny(User $user)
    {
        return $user->role === 'ADMIN';
    }

    public function view(User $user, Paiement $paiement)
    {
        return $user->role === 'ADMIN' || $user->id === $paiement->dette->client->user_id;
    }

    public function create(User $user)
    {
        return true; // Tous les utilisateurs peuvent créer un paiement
    }

    public function update(User $user, Paiement $paiement)
    {
        return $user->role === 'ADMIN';
    }

    public function delete(User $user, Paiement $paiement)
    {
        return $user->role === 'ADMIN';
    }
}
