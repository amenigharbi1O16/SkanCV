<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalysisResource;
use App\Models\Cv;
use App\Models\JobPosting;

class AnalysisController extends Controller
{
    /**
     * GET /job-postings/{jobPosting}/cvs/{cv}/analysis
     * MISSION : consulter le résultat de matching d'un CV.
     * Lecture seule : une Analysis n'est jamais créée via HTTP,
     * uniquement par le pipeline (étape suivante).
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $analysis = $cv->analysis;

        abort_if($analysis === null, 404, "Analyse pas encore disponible pour ce CV.");

        return new AnalysisResource($analysis);
    }
}