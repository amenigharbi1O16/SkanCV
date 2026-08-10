<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Cv représente une soumission de CV pour une offre d'emploi précise.
 *
 * Relations :
 * - CV appartient à 1 JobPosting (belongsTo)
 * - CV a 1 Analysis (hasOne)
 *
 * Rappel métier : un même candidat qui postule à 2 offres différentes
 * crée 2 lignes distinctes dans cette table (2 uploads séparés),
 * même si c'est physiquement le même fichier PDF.
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
            // Même logique que pour required_skills sur JobPosting :
            // JSON en base <-> tableau PHP en mémoire.
            'extracted_skills' => 'array',
        ];
    }

    /**
     * Relation inverse de JobPosting::cvs().
     * Un CV appartient à exactement une offre d'emploi.
     *
     * Eloquent déduit la clé étrangère à partir du nom de la méthode
     * (jobPosting -> job_posting_id), donc pas besoin de la préciser.
     *
     * Utilisation : $cv->jobPosting récupère l'objet JobPosting lié.
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Relation hasOne : un CV a au maximum une analyse.
     *
     * C'est la traduction Eloquent de la contrainte
     * ->unique() qu'on a mise sur analyses.cv_id au niveau SQL.
     * Attention : hasOne() côté PHP ne suffit PAS à garantir
     * l'unicité (ce n'est qu'une facilité de navigation) — c'est
     * bien la contrainte SQL unique() qui empêche réellement la
     * création d'une deuxième analyse pour le même CV.
     *
     * Utilisation : $cv->analysis récupère l'objet Analysis lié
     * (ou null si l'analyse n'a pas encore été créée/traitée).
     */
    public function analysis(): HasOne
    {
        return $this->hasOne(Analysis::class);
    }
}