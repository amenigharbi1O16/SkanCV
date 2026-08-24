<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MISSION : formater le résultat d'analyse IA pour le frontend HR.
 */
class AnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'cv_id'             => $this->cv_id,
            'status'            => $this->status->value,
            'similarity_score'  => $this->similarity_score,
            'justification'     => $this->justification,
            'matching_skills'   => $this->matching_skills ?? [],
            'missing_skills'    => $this->missing_skills ?? [],
            'analyzed_at'       => $this->analyzed_at?->toIso8601String(),
            'candidate_name'    => $this->whenLoaded('cv', fn () => $this->cv?->candidate_name),
            'candidate_email'   => $this->whenLoaded('cv', fn () => $this->cv?->candidate_email),
            'job_posting_id'    => $this->whenLoaded('cv', fn () => $this->cv?->job_posting_id),
            'job_posting_title' => $this->whenLoaded('cv', fn () => $this->cv?->jobPosting?->title),
        ];
    }
}
