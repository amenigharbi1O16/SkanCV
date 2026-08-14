<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MISSION : stocker le résultat immuable d'une analyse IA (1 CV = 1 analyse).
 *
 * Créée en PENDING par CvController@store, enrichie par ProcessCvAnalysis.
 * Jamais modifiée via HTTP — conforme au cahier des charges Anypli.
 */
class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'status',
        'similarity_score',
        'justification',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => AnalysisStatus::class, // Cast le statut en Enum
            'similarity_score' => 'float',               // Cast le score en float
            'analyzed_at'      => 'datetime',            // Cast la date d'analyse en datetime
        ];
    }

    /**
     * Relation : Une analyse appartient à un CV.
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}