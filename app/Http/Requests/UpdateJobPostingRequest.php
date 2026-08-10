<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateJobPostingRequest valide les données lors de la MODIFICATION
 * d'une offre existante (PUT/PATCH /api/job-postings/{id}).
 *
 * Différence clé avec StoreJobPostingRequest : on utilise 'sometimes'
 * au lieu de 'required'. Ça permet une mise à jour PARTIELLE — le
 * HR Staff peut vouloir modifier uniquement le titre, sans être
 * obligé de renvoyer TOUS les champs à chaque fois.
 */
class UpdateJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'sometimes' : la règle qui suit (ex: string|max:255)
            // ne s'applique QUE SI le champ est présent dans la
            // requête. Si le champ est absent, aucune erreur —
            // le champ concerné ne sera simplement pas modifié.
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'required_skills' => 'sometimes|required|array',
            'required_skills.*' => 'required|string',
        ];
    }
}