<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCvRequest extends FormRequest
{
    /**
     * Autorise l'action de soumettre un CV.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation du formulaire de dépôt de CV.
     */
    public function rules(): array
    {
        return [
            'candidate_name'  => ['required', 'string', 'max:255'],
            'candidate_email' => ['required', 'email', 'max:255'],
            'file' => [
                'required',
                'file',
                'mimes:pdf',   // Vérifie que le fichier est bien un PDF
                'max:5120',    // Taille max de 5 Mo
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés pour la validation.
     */
    public function messages(): array
    {
        return [
            'candidate_email.email' => 'L\'email fourni n\'est pas valide.',
            'file.mimes'            => 'Le fichier doit être un PDF valide.',
            'file.max'              => 'Le fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}
