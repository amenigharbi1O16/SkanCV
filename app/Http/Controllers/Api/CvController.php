<?php

namespace App\Http\Controllers\Api;

use App\Enums\AnalysisStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCvRequest;
use App\Http\Resources\CvResource;
use App\Jobs\ProcessCvAnalysis;
use App\Models\Analysis;
use App\Models\Cv;
use App\Models\JobPosting;
use App\Notifications\CvAnalysisStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreCvBatchRequest;
use Illuminate\Support\Facades\DB;
class CvController extends Controller
{
    /**
     * GET /job-postings/{jobPosting}/cvs
     * MISSION : lister les candidatures reçues pour une offre.
     * with('analysis') évite le N+1 : sans ça, chaque CV déclencherait
     * une requête SQL séparée pour vérifier s'il a une analyse.
     */
    public function index(Request $request, JobPosting $jobPosting)
    {
        $query = $jobPosting->cvs()->with('analysis');

        if ($request->query('sort') === 'score') {
            $direction = $request->query('order', 'desc') === 'asc' ? 'asc' : 'desc';

            $query->leftJoin('analyses', 'cvs.id', '=', 'analyses.cv_id')
                ->orderByRaw('analyses.similarity_score IS NULL')
                ->orderBy('analyses.similarity_score', $direction)
                ->select('cvs.*');
        } else {
            $query->latest();
        }

        return CvResource::collection($query->get());
    }

    /**
     * POST /job-postings/{jobPosting}/cvs
     * MISSION : stocker le PDF de façon privée, créer le CV et lancer l'analyse.
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
            'uploaded_by'     => $request->user()->id,
        ]);

        $analysis = Analysis::create([
            'cv_id'  => $cv->id,
            'status' => AnalysisStatus::PENDING,
        ]);

        // Notification in-app : le HR sait immédiatement que le CV est en file d'attente
        $request->user()->notify(new CvAnalysisStatusNotification($analysis));

        ProcessCvAnalysis::dispatch($cv);

        return (new CvResource($cv->fresh('analysis')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /job-postings/{jobPosting}/cvs/{cv}
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
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

        Storage::disk('local')->delete($cv->file_path);

        $cv->delete();

        return response()->noContent();
    }

    /**
    * Upload multiple CVs pour une même offre en un seul appel.
    * Réutilise exactement la même logique que store() mais en boucle,
    * dans une transaction pour garantir l'atomicité (soit tout est créé, soit rien).
    */
    public function storeBatch(StoreCvBatchRequest $request, JobPosting $jobPosting)
    {
        $files = $request->file('files');
        $names = $request->input('candidate_names', []);
        $emails = $request->input('candidate_emails', []);

        $createdCvs = [];

        DB::transaction(function () use ($files, $names, $emails, $jobPosting, $request, &$createdCvs) {
            foreach ($files as $index => $file) {
                $path = $file->store('cvs', 'local');

                $candidateEmail = $emails[$index] ?? null;
                if (empty($candidateEmail)) {
                    $candidateEmail = 'batch-cv-'.uniqid().'@skancv.local';
                }

                $cv = Cv::create([
                    'job_posting_id' => $jobPosting->id,
                    'uploaded_by' => $request->user()->id,
                    'candidate_name' => $names[$index] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'candidate_email' => $candidateEmail,
                    'file_path' => $path,
                ]);

                Analysis::create([
                    'cv_id' => $cv->id,
                    'status' => AnalysisStatus::PENDING,
                ]);

                $createdCvs[] = $cv->fresh(['analysis']);
            }
        });

        $uploader = $request->user();
        foreach ($createdCvs as $cv) {
            if ($cv->analysis) {
                $uploader->notify(new CvAnalysisStatusNotification($cv->analysis));
            }
        }

        foreach ($createdCvs as $cv) {
            ProcessCvAnalysis::dispatch($cv);
        }

        return CvResource::collection(collect($createdCvs))
            ->additional([
                'message' => count($createdCvs).' CVs mis en file d\'attente pour analyse.',
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
