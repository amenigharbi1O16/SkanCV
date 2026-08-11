<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modèle représentant un CV soumis pour une offre d'emploi.
 */
class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'candidate_name',
        'candidate_email',
        'file_path',
        'extracted_text',
        'extracted_skills',
    ];

    protected function casts(): array
    {
        return [
            'extracted_skills' => 'array', // Cast JSON en tableau PHP
        ];
    }

    /**
     * Relation : Un CV appartient à une offre d'emploi.
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Relation : Un CV a une seule analyse associée.
     */
    public function analysis(): HasOne
    {
        return $this->hasOne(Analysis::class);
    }
}