<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/**
 * @OA\Info(title="API Authentification", version="1.0.0")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authentifie un utilisateur et retourne un jeton d'accès et un jeton de rafraîchissement.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="mot_de_passe", type="string", format="password", example="password123"),
     *                 required={"email", "mot_de_passe"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse réussie avec les jetons",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"),
     *             @OA\Property(property="refresh_token", type="string", example="d2ViOGQyOGY0Y2FlZTc2MGJjMzk5YzY4NTczZDk5OTY="),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Informations de connexion incorrectes",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Les informations de connexion sont incorrectes.")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Rafraîchit le jeton d'accès avec un jeton de rafraîchissement.",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="refresh_token", type="string", example="d2ViOGQyOGY0Y2FlZTc2MGJjMzk5YzY4NTczZDk5OTY="),
     *                 required={"refresh_token"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse réussie avec le nouveau jeton d'accès",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Jeton de rafraîchissement invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Jeton de rafraîchissement invalide.")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnecte un utilisateur en révoquant ses jetons.",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié.")
     *         )
     *     )
     * )
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
