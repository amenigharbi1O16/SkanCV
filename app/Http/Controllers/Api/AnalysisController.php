<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalysisResource;
use App\Models\Analysis;
use App\Models\Cv;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    /**
     * GET /api/analyses
     * MISSION : historique filtrable de toutes les analyses (immuable).
     * Filtres : job_posting_id, candidate_email
     */
    public function index(Request $request)
    {
        $query = Analysis::query()
            ->with(['cv.jobPosting'])
            ->latest();

        if ($request->filled('job_posting_id')) {
            $query->whereHas('cv', function ($q) use ($request) {
                $q->where('job_posting_id', $request->integer('job_posting_id'));
            });
        }

        if ($request->filled('candidate_email')) {
            $query->whereHas('cv', function ($q) use ($request) {
                $q->where('candidate_email', $request->string('candidate_email'));
            });
        }

        return AnalysisResource::collection(
            $query->paginate($request->integer('per_page', 15))
        );
    }

    /**
     * GET /job-postings/{jobPosting}/cvs/{cv}/analysis
     * MISSION : consulter le résultat de matching d'un CV.
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $analysis = $cv->analysis;

        abort_if($analysis === null, 404, "Analyse pas encore disponible pour ce CV.");

        return new AnalysisResource($analysis);
    }
}
