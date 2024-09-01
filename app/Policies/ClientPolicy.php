<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user)
    {
        return $user->role === 'ADMIN';
    }

    public function view(User $user, Client $client)
    {
        return $user->role === 'ADMIN' || $user->id === $client->user_id;
    }

    public function create(User $user)
    {
        return $user->role === 'ADMIN';
    }

    public function update(User $user, Client $client)
    {
        return $user->role === 'ADMIN' || $user->id === $client->user_id;
    }

    public function delete(User $user, Client $client)
    {
        return $user->role === 'ADMIN';
    }
}
