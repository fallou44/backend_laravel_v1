<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Seuls les ADMIN peuvent voir la liste des utilisateurs
        return $user->role === 'ADMIN';
    }

    public function view(User $user, User $model)
    {
        // Les utilisateurs peuvent voir leur propre profil ou les admins peuvent voir tous les profils
        return $user->id === $model->id || $user->role === 'ADMIN';
    }

    public function create(User $user)
    {
        // Seuls les ADMIN peuvent créer des utilisateurs avec des rôles ADMIN ou BOUTIQUIER
        return $user->role === 'ADMIN';
    }

    public function update(User $user, User $model)
    {
        // Les utilisateurs peuvent mettre à jour leur propre profil ou les admins peuvent mettre à jour tous les profils
        return $user->id === $model->id || $user->role === 'ADMIN';
    }

    public function delete(User $user, User $model)
    {
        // Les ADMIN peuvent supprimer tous les utilisateurs
        // Les BOUTIQUIER peuvent supprimer uniquement les clients, mais pas d'autres BOUTIQUIERS ou ADMIN
        if ($user->role === 'ADMIN') {
            return true;
        }

        if ($user->role === 'BOUTIQUIER') {
            // Le boutiquier ne peut pas supprimer d'autres boutiquiers ou admins
            return $model->role !== 'ADMIN' && $model->role !== 'BOUTIQUIER';
        }

        return false;
    }

    public function restore(User $user, User $model)
    {
        // Seuls les ADMIN peuvent restaurer des utilisateurs supprimés
        return $user->role === 'ADMIN';
    }

    public function forceDelete(User $user, User $model)
    {
        // Seuls les ADMIN peuvent supprimer définitivement des utilisateurs
        return $user->role === 'ADMIN';
    }
}
