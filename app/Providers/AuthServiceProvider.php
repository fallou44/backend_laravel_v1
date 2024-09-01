<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\User;
use App\Models\Client;
use App\Policies\UserPolicy;
use App\Policies\ClientPolicy;
use Laravel\Passport\Passport;
use App\Policies\ArticlePolicy;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Client::class => ClientPolicy::class,
        Article::class => ArticlePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addMinutes(5)); // Durée de vie des jetons d'accès
        Passport::refreshTokensExpireIn(now()->addDays(30)); // Durée de vie des jetons de rafraîchissement
        Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // Durée de vie des jetons personnels
    }
}
