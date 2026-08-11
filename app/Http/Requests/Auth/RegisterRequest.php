<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Autorise l'inscription de l'utilisateur.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la création de compte.
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)], // Exige un champ password_confirmation
        ];
    }

    /**
     * Messages d'erreur personnalisés de validation.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Un compte existe déjà avec cet email.',
        ];
    }
}