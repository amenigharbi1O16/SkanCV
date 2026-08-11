<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisResource extends JsonResource
{
    /**
     * Convertit la ressource en tableau.
     */
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