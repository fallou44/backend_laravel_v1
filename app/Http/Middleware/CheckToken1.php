<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Laravel\Passport\Token;

class CheckToken
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->token()) {
            return response()->json(['message' => 'Non autorisé'], 401);
        }

        $token = $request->user()->token();
        $tokenCreatedAt = Carbon::parse($token->created_at);

        // Si le token a été créé il y a plus de 30 minutes, on le rafraîchit
        if (now()->diffInMinutes($tokenCreatedAt) >= 30) {
            // Révoquer l'ancien token
            $token->revoke();

            // Créer un nouveau token
            $newToken = $request->user()->createToken('auth_token', ['*']);

            // Mettre à jour le token dans la réponse
            $response = $next($request);
            $response->header('Authorization', 'Bearer ' . $newToken->accessToken);

            return $response;
        }

        return $next($request);
    }
}
