<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Cv;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_analysis_of_a_cv(): void
    {
        $user = User::factory()->create();
        $jobPosting = JobPosting::factory()->for($user)->create();
        $cv = Cv::factory()->for($jobPosting)->create();
        $token = auth('api')->login($user);
    
        Analysis::factory()->create([
            'cv_id' => $cv->id,
            'status' => AnalysisStatus::COMPLETED,
            'similarity_score' => 0.72,
        ]);
    
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/job-postings/{$cv->job_posting_id}/cvs/{$cv->id}/analysis");
    
        // assertJsonPath cible une valeur précise dans l'arbre JSON, plus
        // fiable ici que assertJsonFragment qui compare un encodage exact.
        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'completed')
                 ->assertJsonPath('data.similarity_score', '0.7200');
    }

    public function test_returns_404_if_no_analysis_exists(): void
    {
        $user = User::factory()->create();
        $jobPosting = JobPosting::factory()->for($user)->create();
        $cv = Cv::factory()->for($jobPosting)->create();
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/job-postings/{$cv->job_posting_id}/cvs/{$cv->id}/analysis");

        $response->assertStatus(404);
    }
}