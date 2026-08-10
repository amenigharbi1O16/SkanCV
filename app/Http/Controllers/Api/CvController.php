<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCvRequest;
use App\Http\Resources\CvResource;
use App\Models\Cv;
use App\Models\JobPosting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CvController extends Controller
{
    /**
     * GET /job-postings/{jobPosting}/cvs
     * MISSION : lister les candidatures reçues pour une offre.
     * with('analysis') évite le N+1 : sans ça, chaque CV déclencherait
     * une requête SQL séparée pour vérifier s'il a une analyse.
     */
    public function index(JobPosting $jobPosting)
    {
        $cvs = $jobPosting->cvs()->with('analysis')->latest()->get();

        return CvResource::collection($cvs);
    }

    /**
     * POST /job-postings/{jobPosting}/cvs
     * MISSION : stocker le PDF de façon privée + créer le CV.
     * extracted_text et extracted_skills restent NULL ici : ils seront
     * remplis par le Job en queue à l'étape suivante (appel FastAPI /extract).
     */
    public function store(StoreCvRequest $request, JobPosting $jobPosting)
    {
        $file = $request->file('file');

        // storage/app/private/cvs/xxxxx.pdf — nom généré par Laravel,
        // jamais de collision, jamais d'URL publique.
        $path = $file->store('cvs', 'local');

        $cv = $jobPosting->cvs()->create([
            'candidate_name'  => $request->validated('candidate_name'),
            'candidate_email' => $request->validated('candidate_email'),
            'file_path'       => $path,
            // extracted_text / extracted_skills : absents ici volontairement,
            // NULL par défaut tant que le pipeline n'est pas passé.
        ]);

        // TODO (étape suivante — queues Redis) :
        // ProcessCvAnalysis::dispatch($cv);
        // Ce Job appellera FastAPI /extract (remplit extracted_text/extracted_skills
        // sur ce Cv), puis /score (crée l'Analysis correspondante).

        return (new CvResource($cv))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /job-postings/{jobPosting}/cvs/{cv}
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        // Vérifie que ce CV appartient bien à cette offre.
        // Sans ce contrôle, n'importe quel cv_id valide serait accessible
        // via n'importe quelle URL d'offre.
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $cv->load('analysis');

        return new CvResource($cv);
    }

    /**
     * DELETE /job-postings/{jobPosting}/cvs/{cv}
     */
    public function destroy(JobPosting $jobPosting, Cv $cv)
    {
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        // Fichier supprimé AVANT le record, sinon on perd file_path.
        Storage::disk('local')->delete($cv->file_path);

        $cv->delete(); // cascadeOnDelete() supprime l'Analysis liée en DB

        return response()->noContent(); // 204
    }
}