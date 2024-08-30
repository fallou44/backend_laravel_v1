<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CheckToken
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->currentAccessToken()) {
            return response()->json(['message' => 'Non autorisé'], 401);
        }

        $token = $request->user()->currentAccessToken();
        $tokenCreatedAt = Carbon::parse($token->created_at);

        // Si le token a été créé il y a plus de 30 minutes, on le rafraîchit
        if (now()->diffInMinutes($tokenCreatedAt) >= 30) {
            // Supprimer l'ancien token
            $token->delete();

            // Créer un nouveau token
            $newToken = $request->user()->createToken('auth_token', ['*'], now()->addMinutes(30));

            // Mettre à jour le token dans la requête
            $request->headers->set('Authorization', 'Bearer ' . $newToken->plainTextToken);

            // Mettre à jour le token pour les prochaines requêtes dans cette session
            $request->setUserResolver(function () use ($newToken) {
                return (new PersonalAccessToken())->findToken($newToken->plainTextToken)->tokenable;
            });
        }

        return $next($request);
    }
}
