<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalysisResource;
use App\Models\Cv;
use App\Models\JobPosting;

class AnalysisController extends Controller
{
    /**
     * Affiche l'analyse associée à un CV spécifique.
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        // Vérifie la relation de dépendance entre le CV et l'offre
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $analysis = $cv->analysis;

        // Retourne une erreur si l'analyse n'est pas encore disponible
        abort_if($analysis === null, 404, "Analyse pas encore disponible pour ce CV.");

        return new AnalysisResource($analysis);
    }
}