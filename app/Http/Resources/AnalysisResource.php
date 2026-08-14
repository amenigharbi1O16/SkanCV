<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * MISSION : formater le résultat d'analyse IA pour le frontend HR.
 * Expose score, justification et statut — jamais les données brutes internes.
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
            'analyzed_at'       => $this->analyzed_at?->toIso8601String(),
        ];
    }
}
