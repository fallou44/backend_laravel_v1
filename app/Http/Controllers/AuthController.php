<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Authentifie un utilisateur et retourne un jeton d'accès et un jeton de rafraîchissement.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'mot_de_passe');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['mot_de_passe'], $user->mot_de_passe)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont incorrectes.'],
            ]);
        }

        // Crée un jeton d'accès et un jeton de rafraîchissement
        $tokenResult = $user->createToken('Personal Access Token');
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->token->refreshToken(); // Crée un jeton de rafraîchissement

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.tokens.expire_in', 300), // Durée de vie du jeton d'accès (5 minutes)
        ]);
    }

    /**
     * Rafraîchit le jeton d'accès avec un jeton de rafraîchissement.
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        // Trouve le jeton de rafraîchissement
        $token = \Laravel\Passport\Token::where('id', $refreshToken)->first();

        if (!$token || !$token->isValid()) {
            return response()->json(['error' => 'Jeton de rafraîchissement invalide.'], 401);
        }

        $user = $token->user;
        $newTokenResult = $user->createToken('Personal Access Token');
        $newAccessToken = $newTokenResult->accessToken;

        return response()->json([
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.tokens.expire_in', 300), // Durée de vie du nouveau jeton d'accès (5 minutes)
        ]);
    }

    /**
     * Déconnecte un utilisateur en révoquant ses jetons.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Révoque tous les jetons de l'utilisateur
        foreach ($user->tokens as $token) {
            $token->delete();
        }

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
