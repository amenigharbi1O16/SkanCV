<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * JobPosting représente une offre d'emploi publiée par un HR Staff.
 *
 * Relation : 1 JobPosting -- * CV
 * (une offre reçoit plusieurs CVs, chaque CV appartient à une seule offre)
 */
class JobPosting extends Model
{
    // HasFactory permet de générer des données de test via des
    // Factories (utile pour les tests unitaires/feature plus tard).
    use HasFactory;

    /**
     * $fillable = liste blanche des champs autorisés à être remplis
     * via l'assignation de masse (JobPosting::create([...])).
     *
     * C'est une protection de sécurité obligatoire : sans ça, Eloquent
     * refuse par défaut le mass assignment (erreur
     * MassAssignmentException), pour éviter qu'un attaquant injecte
     * des champs non prévus (ex: id, timestamps) via une requête HTTP
     * malveillante.
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'required_skills',
    ];

    /**
     * $casts définit comment Eloquent doit convertir automatiquement
     * les valeurs entre la base de données (SQL) et PHP.
     *
     * 'required_skills' => 'array' :
     * En base, la colonne est de type JSON (ex: '["PHP","Laravel"]').
     * Sans ce cast, Eloquent te renverrait cette valeur comme une
     * STRING brute JSON. Avec le cast, $jobPosting->required_skills
     * te donne directement un tableau PHP natif (['PHP', 'Laravel']),
     * et à l'inverse, si tu assignes un tableau PHP, Eloquent
     * l'encode automatiquement en JSON avant l'INSERT/UPDATE.
     */
    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
        ];
    }

    /**
     * Relation hasMany : une offre d'emploi a plusieurs CVs soumis.
     *
     * Eloquent déduit automatiquement la clé étrangère à utiliser
     * (job_posting_id) à partir du nom de la méthode/classe courante
     * (JobPosting -> job_posting_id), donc pas besoin de la préciser
     * explicitement ici (mais on pourrait avec
     * hasMany(Cv::class, 'job_posting_id') si on voulait être
     * totalement explicite).
     *
     * Utilisation : $jobPosting->cvs récupère une Collection de
     * tous les CVs liés à cette offre.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }

    /**
     * Limite la résolution de route aux offres appartenant à l'utilisateur connecté.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->where($field ?? $this->getRouteKeyName(), $value);

        if (Auth::guard('api')->check()) {
            $query->where('user_id', Auth::guard('api')->id());
        }

        return $query->firstOrFail();
    }
}