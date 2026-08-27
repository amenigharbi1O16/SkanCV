<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use App\Support\SkillNormalizer;
use Illuminate\Http\JsonResponse;

/**
 * MISSION : CRUD des offres d'emploi créées par le HR Staff.
 * Chaque offre définit les compétences requises utilisées plus tard
 * par ProcessCvAnalysis pour scorer les CVs uploadés.
 */
class JobPostingController extends Controller
{
    /** GET /api/job-postings — liste toutes les offres. */
    public function index(): JsonResponse
    {
        $jobPostings = JobPosting::all();

        // ->response() déclenche le pipeline complet de transformation
        // Laravel, qui applique le wrapping standard { "data": [...] }.
        return JobPostingResource::collection($jobPostings)->response();
    }

    /** POST /api/job-postings — crée une offre avec required_skills (JSON array). */
    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['required_skills'] = SkillNormalizer::normalize($data['required_skills']);

        $jobPosting = JobPosting::create($data);

        return response()->json(
            new JobPostingResource($jobPosting),
            201
        );
    }

    /** GET /api/job-postings/{id} — détail d'une offre. */
    public function show(JobPosting $jobPosting): JsonResponse
    {
        return response()->json(new JobPostingResource($jobPosting));
    }

    /** PUT/PATCH /api/job-postings/{id} — met à jour partiellement ou totalement. */
    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['required_skills'])) {
            $data['required_skills'] = SkillNormalizer::normalize($data['required_skills']);
        }

        $jobPosting->update($data);

        return response()->json(new JobPostingResource($jobPosting));
    }

    /** DELETE /api/job-postings/{id} — supprime l'offre et ses CVs (cascade). */
    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        $jobPosting->delete();

        return response()->json(null, 204);
    }
}
