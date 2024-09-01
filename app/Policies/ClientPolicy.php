<?php
namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // L'ADMIN et le BOUTIQUIER peuvent voir la liste des clients
        return in_array($user->role, ['ADMIN', 'BOUTIQUIER']);
    }

    public function view(User $user, Client $client)
    {
        // Seul le propriétaire du client ou les ADMIN peuvent voir les détails du client
        return $user->id === $client->user_id || in_array($user->role, ['ADMIN', 'BOUTIQUIER']);
    }

    public function create(User $user)
    {
        // Seuls les ADMIN peuvent créer un client
        return $user->role === 'ADMIN' || $user->role === 'BOUTIQUIER';
    }

    public function update(User $user, Client $client)
    {
        // Seul le propriétaire du client ou les ADMIN peuvent mettre à jour le client
        return $user->id === $client->user_id || in_array($user->role, ['ADMIN', 'BOUTIQUIER']);
    }

    public function delete(User $user, Client $client)
    {
        // Seuls les ADMIN peuvent supprimer un client
        return $user->role === 'ADMIN';
    }
}
