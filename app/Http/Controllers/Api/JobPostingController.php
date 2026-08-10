<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;

class JobPostingController extends Controller
{
    public function index(): JsonResponse
    {
        $jobPostings = JobPosting::all();

        return response()->json(
            JobPostingResource::collection($jobPostings)
        );
    }

    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        $jobPosting = JobPosting::create($request->validated());

        return response()->json(
            new JobPostingResource($jobPosting),
            201
        );
    }

    public function show(JobPosting $jobPosting): JsonResponse
    {
        return response()->json(new JobPostingResource($jobPosting));
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): JsonResponse
    {
        $jobPosting->update($request->validated());

        return response()->json(new JobPostingResource($jobPosting));
    }

    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        $jobPosting->delete();

        return response()->json(null, 204);
    }
}
