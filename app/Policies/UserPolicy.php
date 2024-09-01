<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Par exemple, les utilisateurs avec le rôle ADMIN peuvent voir tous les utilisateurs
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Les utilisateurs peuvent voir leur propre profil ou les admins peuvent voir tous les profils
        return $user->id === $model->id || $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Par exemple, tous les utilisateurs peuvent créer des profils sauf les admins
        return $user->role !== 'ADMIN';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Les utilisateurs peuvent mettre à jour leur propre profil ou les admins peuvent mettre à jour tous les profils
        return $user->id === $model->id || $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Seuls les utilisateurs avec le rôle ADMIN peuvent supprimer des utilisateurs
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // Seuls les utilisateurs avec le rôle ADMIN peuvent restaurer des utilisateurs supprimés
        return $user->role === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Seuls les utilisateurs avec le rôle ADMIN peuvent supprimer définitivement des utilisateurs
        return $user->role === 'ADMIN';
    }
}
