<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessCvAnalysis;
use App\Models\JobPosting;
use App\Models\User;
use App\Notifications\CvAnalysisStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvBatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;
    protected JobPosting $jobPosting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = auth('api')->login($this->user);
        $this->jobPosting = JobPosting::factory()->for($this->user)->create();
        Storage::fake('local');
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_batch_upload_creates_cvs_with_emails_and_dispatches_jobs(): void
    {
        Queue::fake();
        Notification::fake();

        $files = [
            UploadedFile::fake()->create('cv1.pdf', 500, 'application/pdf'),
            UploadedFile::fake()->create('cv2.pdf', 500, 'application/pdf'),
        ];

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/job-postings/{$this->jobPosting->id}/cvs/batch", [
                'files' => $files,
                'candidate_names' => ['Alice Martin', 'Bob Dupont'],
                'candidate_emails' => ['alice@example.com', 'bob@example.com'],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data', 'message']);

        $this->assertDatabaseHas('cvs', [
            'candidate_name' => 'Alice Martin',
            'candidate_email' => 'alice@example.com',
        ]);
        $this->assertDatabaseHas('cvs', [
            'candidate_name' => 'Bob Dupont',
            'candidate_email' => 'bob@example.com',
        ]);
        $this->assertDatabaseCount('analyses', 2);

        Queue::assertPushed(ProcessCvAnalysis::class, 2);
        Notification::assertSentTo(
            User::first(),
            CvAnalysisStatusNotification::class,
            2
        );
    }

    public function test_batch_upload_generates_placeholder_email_when_missing(): void
    {
        Queue::fake();

        $file = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/job-postings/{$this->jobPosting->id}/cvs/batch", [
                'files' => [$file],
            ]);

        $response->assertStatus(201);

        $cv = \App\Models\Cv::first();
        $this->assertNotNull($cv);
        $this->assertStringEndsWith('@skancv.local', $cv->candidate_email);
    }
}
