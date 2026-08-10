<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JobPostingResource définit EXPLICITEMENT la structure JSON envoyée
 * à React pour représenter une JobPosting.
 *
 * C'est une liste blanche : seuls les champs écrits ici sortent en
 * JSON, peu importe ce que contient le Model en base. Ça protège
 * contre l'exposition accidentelle de futurs champs sensibles qu'on
 * ajouterait un jour au Model sans y penser.
 */
class JobPostingResource extends JsonResource
{
    /**
     * toArray() transforme l'objet JobPosting ($this, qui représente
     * ici l'instance du Model) en tableau PHP, que Laravel convertit
     * ensuite automatiquement en JSON dans la réponse HTTP.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            // Pas besoin de json_decode() manuel ici : grâce au cast
            // 'required_skills' => 'array' défini dans le Model
            // (Étape 2), $this->required_skills est DÉJÀ un tableau
            // PHP natif à ce stade. La Resource en profite directement.
            'required_skills' => $this->required_skills,

            // whenLoaded('cvs') : n'inclut le tableau des CVs QUE SI
            // la relation a été explicitement chargée en amont (via
            // ->load('cvs') ou ->with('cvs') dans le Controller).
            // Sans ça, chaque appel à /api/job-postings déclencherait
            // une requête SQL supplémentaire par offre pour compter
            // ses CVs (problème classique dit "N+1 query"), même
            // quand on n'en a pas besoin (ex: sur la page liste).
            'cvs_count' => $this->whenLoaded('cvs', fn () => $this->cvs->count()),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}