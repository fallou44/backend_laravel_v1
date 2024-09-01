<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaiementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any paiement.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // Seuls les ADMIN et les BOUTIQUIER peuvent voir la liste des paiements
        return in_array($user->role, ['ADMIN', 'BOUTIQUIER']);
    }

    /**
     * Determine whether the user can view the paiement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return mixed
     */
    public function view(User $user, Paiement $paiement)
    {
        // Seul le propriétaire du paiement (client) ou les ADMIN et BOUTIQUIER peuvent voir les détails du paiement
        return $user->id === $paiement->dette->client->user_id || in_array($user->role, ['ADMIN', 'BOUTIQUIER']);
    }

    /**
     * Determine whether the user can create a paiement.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // Les BOUTIQUIER et les ADMIN peuvent créer des paiements
        return in_array($user->role, ['ADMIN', 'BOUTIQUIER']);
    }

    /**
     * Determine whether the user can update the paiement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return mixed
     */
    public function update(User $user, Paiement $paiement)
    {
        // Seuls les ADMIN peuvent mettre à jour un paiement
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the paiement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return mixed
     */
    public function delete(User $user, Paiement $paiement)
    {
        // Seuls les ADMIN peuvent supprimer un paiement
        return $user->role === 'ADMIN';
    }
}
