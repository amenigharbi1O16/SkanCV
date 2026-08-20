<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * MISSION : créer un compte HR Staff et retourner immédiatement
     * un token JWT.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $token = auth('api')->login($user);

        return $this->respondWithToken($token, $user, 201);
    }

    /**
     * POST /api/login
     * MISSION : vérifier les identifiants et émettre un JWT.
     * Renvoie 422 pour rester cohérent avec le format d'erreur de
     * validation utilisé sur le reste de l'API.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! $token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        return $this->respondWithToken($token, auth('api')->user());
    }

    /**
     * POST /api/logout
     * MISSION : invalider (blacklister) le token JWT courant.
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->noContent(); // 204
    }

    private function respondWithToken(string $token, User $user, int $status = 200)
    {
        return response()->json([
            'user'         => new UserResource($user),
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
        ], $status);
    }
}