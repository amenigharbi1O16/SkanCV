<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * MISSION : rejeter toute soumission dangereuse AVANT que le controller
     * ne touche au disque ou à la base.
     *
     * candidate_name / candidate_email sont désormais optionnels côté client :
     * le pipeline FastAPI /extract les remplit automatiquement depuis le PDF.
     * Un placeholder est envoyé par le frontend ; le champ sera mis à jour
     * après analyse IA.
     */
    public function rules(): array
    {
        return [
            'candidate_name'  => ['nullable', 'string', 'max:255'],
            'candidate_email' => ['nullable', 'string', 'max:255'],
            'file' => [
                'required',
                'file',
                'mimes:pdf',   // vérifie le contenu réel, pas juste l'extension
                'max:5120',    // 5 Mo (en Ko)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Le fichier doit être un PDF valide.',
            'file.max'   => 'Le fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}