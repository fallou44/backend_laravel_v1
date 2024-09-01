<?php

namespace App\Policies;

use App\Models\Dette;
use App\Models\User;

class DettePolicy
{
    public function viewAny(User $user)
    {
        return $user->role === 'ADMIN';
    }

    public function view(User $user, Dette $dette)
    {
        return $user->role === 'ADMIN' || $user->id === $dette->client->user_id;
    }

    public function create(User $user)
    {
        return $user->role === 'ADMIN';
    }

    public function update(User $user, Dette $dette)
    {
        return $user->role === 'ADMIN';
    }

    public function delete(User $user, Dette $dette)
    {
        return $user->role === 'ADMIN';
    }
}
