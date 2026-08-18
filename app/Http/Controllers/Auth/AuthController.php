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
     * un token JWT, pour que le frontend puisse enchaîner sans
     * repasser par /login juste après l'inscription.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $token = auth('api')->login($user);

        return $this->respondWithToken($user, $token, 201);
    }

    /**
     * POST /api/login
     * MISSION : vérifier les identifiants et émettre un nouveau token JWT.
     */
    public function login(LoginRequest $request)
    {
        $credentials = [
            'email'    => $request->validated('email'),
            'password' => $request->validated('password'),
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        return $this->respondWithToken(auth('api')->user(), $token);
    }

    /**
     * POST /api/logout
     * MISSION : invalider (blacklist) le token JWT utilisé pour cette requête.
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->noContent();
    }

    /**
     * Réponse standard user + token, cohérente avec le format
     * déjà utilisé par register/login.
     */
    protected function respondWithToken(User $user, string $token, int $status = 200)
    {
        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], $status);
    }
}