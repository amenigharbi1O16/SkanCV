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
     * Liste les CVs associés à une offre d'emploi.
     */
    public function index(JobPosting $jobPosting)
    {
        $cvs = $jobPosting->cvs()->with('analysis')->latest()->get();

        return CvResource::collection($cvs);
    }

    /**
     * Enregistre un nouveau CV et stocke le fichier PDF.
     */
    public function store(StoreCvRequest $request, JobPosting $jobPosting)
    {
        $file = $request->file('file');

        // Stockage privé du fichier PDF
        $path = $file->store('cvs', 'local');

        $cv = $jobPosting->cvs()->create([
            'candidate_name'  => $request->validated('candidate_name'),
            'candidate_email' => $request->validated('candidate_email'),
            'file_path'       => $path,
        ]);

        // TODO: Déclencher le job d'analyse asynchrone

        return (new CvResource($cv))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Affiche un CV spécifique.
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        // Vérifie la cohérence de la relation entre le CV et l'offre d'emploi
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $cv->load('analysis');

        return new CvResource($cv);
    }

    /**
     * Supprime un CV et son fichier associé.
     */
    public function destroy(JobPosting $jobPosting, Cv $cv)
    {
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        // Supprime le fichier physique
        Storage::disk('local')->delete($cv->file_path);

        // Supprime l'enregistrement en base de données
        $cv->delete();

        return response()->noContent();
    }
}