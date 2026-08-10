<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CvResource extends JsonResource
{
    /**
     * MISSION : ne JAMAIS exposer file_path (chemin serveur interne)
     * ni extracted_text en entier (potentiellement volumineux, brut).
     * extracted_skills est exposé car c'est un résumé structuré utile
     * au frontend pour un affichage rapide, une fois le pipeline passé.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'job_posting_id'    => $this->job_posting_id,
            'candidate_name'    => $this->candidate_name,
            'candidate_email'   => $this->candidate_email,
            'extracted_skills'  => $this->extracted_skills, // null tant que /extract n'est pas passé
            'analysis_status'   => $this->whenLoaded('analysis', fn () =>
                $this->analysis?->status?->value
            ),
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}