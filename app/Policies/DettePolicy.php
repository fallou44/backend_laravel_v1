<?php

namespace App\Policies;

use App\Models\Dette;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DettePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Seuls les ADMIN peuvent voir la liste des dettes
        return $user->role === 'ADMIN';
    }

    public function view(User $user, Dette $dette)
    {
        // Seul le propriétaire de la dette (client) ou les ADMIN peuvent voir les détails de la dette
        return $user->id === $dette->client->user_id || $user->role === 'ADMIN';
    }

    public function create(User $user)
    {
        // Seuls les ADMIN peuvent créer une dette
        return $user->role === 'ADMIN' || $user->role === 'BOUTIQUIER';
    }

    public function update(User $user, Dette $dette)
    {
        // Seuls les ADMIN peuvent mettre à jour une dette
        return $user->role === 'ADMIN' || $user->role === 'BOUTIQUIER';
    }

    public function delete(User $user, Dette $dette)
    {
        // Seuls les ADMIN peuvent supprimer une dette
        return $user->role === 'ADMIN';
    }
}
