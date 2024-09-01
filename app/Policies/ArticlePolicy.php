<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Tous les utilisateurs peuvent voir la liste des articles
        return true;
    }

    public function view(User $user, Article $article)
    {
        // Tous les utilisateurs peuvent voir un article spécifique
        return true;
    }

    public function create(User $user)
    {
        // Seuls les BOUTIQUIER et ADMIN peuvent créer un article
        return in_array($user->role, ['BOUTIQUIER', 'ADMIN']);
    }

    public function update(User $user, Article $article)
    {
        // Seuls les BOUTIQUIER et ADMIN peuvent mettre à jour un article
        return in_array($user->role, ['BOUTIQUIER', 'ADMIN']);
    }

    public function delete(User $user, Article $article)
    {
        // Seuls les BOUTIQUIER et ADMIN peuvent supprimer un article
        return in_array($user->role, ['BOUTIQUIER', 'ADMIN']);
    }
}
