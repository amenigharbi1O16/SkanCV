<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * MISSION : valider les identifiants avant AuthController@login.
 * Route publique — pas de token requis.
 */
class LoginRequest extends FormRequest
{
    /**
     * Autorise la connexion de l'utilisateur.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la connexion.
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}