<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user], 201);
    }


    public function login(Request $request)
    {

        Log::info('Tentative de connexion pour : ' . $request->email);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::info('Échec de l\'authentification pour : ' . $request->email);
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        Log::info('Utilisateur authentifié : ' . $user->id);

        try {
            // Créer un token d'accès
            $accessToken = $user->createToken('auth_token')->plainTextToken;

            // Créer un token de rafraîchissement
            $refreshToken = $user->createToken('refresh_token')->plainTextToken;

            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'valid_answers_count' => $user->valid_answers_count,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création des tokens : ' . $e->getMessage());
            Log::error('Stack trace : ' . $e->getTraceAsString());
            return response()->json(['message' => 'Erreur lors de la création des tokens', 'error' => $e->getMessage()], 500);
        }
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->bearerToken();


        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token manquant'], 401);
        }

        $token = PersonalAccessToken::findToken($refreshToken);

        if (!$token || $token->name !== 'refresh_token' || $token->expires_at < now()) {
            return response()->json(['message' => 'Refresh token invalide ou expiré'], 401);
        }

        $user = $token->tokenable;

        // Révoquer l'ancien refresh token
        $token->delete();

        // Révoquer les anciens access tokens (optionnel)
        $user->tokens()->where('name', 'auth_token')->delete();

        // Créer un nouveau access token
        $newAccessToken = $user->createToken('auth_token', ['*'], now()->addMinutes(config('sanctum.expiration', 15)));

        // Créer un nouveau refresh token
        $newRefreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(config('sanctum.refresh_ttl', 7)));

        return response()->json([
            'access_token' => $newAccessToken->plainTextToken,
            'refresh_token' => $newRefreshToken->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
