<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CvResource extends JsonResource
{
    /**
     * Convertit la ressource en tableau.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'job_posting_id'    => $this->job_posting_id,
            'candidate_name'    => $this->candidate_name,
            'candidate_email'   => $this->candidate_email,
            'extracted_skills'  => $this->extracted_skills, // Null tant que l'extraction n'a pas eu lieu
            'analysis_status'   => $this->whenLoaded('analysis', fn () =>
                $this->analysis?->status?->value
            ),
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}