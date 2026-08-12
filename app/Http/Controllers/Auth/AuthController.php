<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * MISSION : créer un compte HR Staff et retourner immédiatement
     * un token d'accès, pour que le frontend puisse enchaîner sans
     * repasser par /login juste après l'inscription.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        // createToken() vient de HasApiTokens (Sanctum) sur le modèle User.
        // Le nom "api-token" est arbitraire, sert juste à identifier le
        // token dans la table personal_access_tokens (colonne "name").
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/login
     * MISSION : vérifier les identifiants et émettre un nouveau token.
     * Sanctum autorise plusieurs tokens actifs par utilisateur en
     * parallèle (multi-appareils) : on ne révoque pas les anciens ici.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            // ValidationException produit une 422 avec le même format
            // que les erreurs de validation classiques, cohérent avec
            // le reste de l'API (JobPosting/Cv/Analysis en 422 aussi).
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * POST /api/logout
     * MISSION : révoquer UNIQUEMENT le token utilisé pour cette requête,
     * pas tous les tokens de l'utilisateur (sinon on déconnecterait
     * ses autres sessions/appareils sans qu'il l'ait demandé).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
