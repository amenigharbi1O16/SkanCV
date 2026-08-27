<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_own_job_postings(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        JobPosting::factory()->count(2)->for($userA)->create();
        JobPosting::factory()->count(3)->for($userB)->create();

        $tokenA = auth('api')->login($userA);

        $response = $this->withHeader('Authorization', "Bearer $tokenA")
            ->getJson('/api/job-postings');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_access_another_users_job_posting(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $jobPosting = JobPosting::factory()->for($owner)->create();

        $token = auth('api')->login($intruder);

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/job-postings/{$jobPosting->id}")
            ->assertStatus(404);
    }

    public function test_user_cannot_access_another_users_cv(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $jobPosting = JobPosting::factory()->for($owner)->create();
        $cv = Cv::factory()->for($jobPosting)->create();

        $token = auth('api')->login($intruder);

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/job-postings/{$jobPosting->id}/cvs/{$cv->id}")
            ->assertStatus(404);
    }

    public function test_user_only_sees_own_analyses_in_history(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $jobA = JobPosting::factory()->for($userA)->create();
        $jobB = JobPosting::factory()->for($userB)->create();

        $cvA = Cv::factory()->for($jobA)->create();
        $cvB = Cv::factory()->for($jobB)->create();

        \App\Models\Analysis::factory()->create(['cv_id' => $cvA->id]);
        \App\Models\Analysis::factory()->create(['cv_id' => $cvB->id]);

        $tokenA = auth('api')->login($userA);

        $response = $this->withHeader('Authorization', "Bearer $tokenA")
            ->getJson('/api/analyses');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_email_must_be_unique_on_register(): void
    {
        User::factory()->create(['email' => 'hr@company.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Other User',
            'email' => 'hr@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
