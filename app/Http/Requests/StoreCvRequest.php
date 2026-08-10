<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pas d'auth candidat prévue dans le scope actuel.
        // Sera revu quand l'auth HR Staff (JWT/Sanctum/Passport) sera tranchée.
        return true;
    }

    /**
     * MISSION : rejeter toute soumission incomplète ou dangereuse AVANT
     * que le controller ne touche au disque ou à la base.
     * On ne valide QUE candidate_name/candidate_email/file : extracted_text
     * et extracted_skills ne sont jamais fournis par le client, ils sont
     * remplis plus tard par le pipeline FastAPI côté serveur.
     */
    public function rules(): array
    {
        return [
            'candidate_name'  => ['required', 'string', 'max:255'],
            'candidate_email' => ['required', 'email', 'max:255'],
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
            'candidate_email.email' => 'L\'email fourni n\'est pas valide.',
            'file.mimes'            => 'Le fichier doit être un PDF valide.',
            'file.max'              => 'Le fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}