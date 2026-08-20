<?php

namespace Tests\Feature;

use App\Jobs\ProcessCvAnalysis;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CvTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected JobPosting $jobPosting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = auth('api')->login(User::factory()->create());
        $this->jobPosting = JobPosting::factory()->create();
        Storage::fake('local');
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    /**
     * L'upload doit créer le Cv ET une Analysis en PENDING, et dispatcher
     * ProcessCvAnalysis — SANS l'exécuter réellement ici (Queue::fake()).
     * Le comportement du Job lui-même (succès/échec/retries) est déjà
     * couvert par ProcessCvAnalysisTest ; ce test isole uniquement la
     * responsabilité du contrôleur.
     */
    public function test_uploading_cv_creates_pending_analysis(): void
    {
        Queue::fake();

        $file = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/job-postings/{$this->jobPosting->id}/cvs", [
                'candidate_name' => 'Ahmed Ben Ali',
                'candidate_email' => 'ahmed.benali@example.com',
                'file' => $file,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('cvs', [
            'job_posting_id' => $this->jobPosting->id,
            'candidate_name' => 'Ahmed Ben Ali',
            'candidate_email' => 'ahmed.benali@example.com',
        ]);

        $this->assertDatabaseHas('analyses', ['status' => 'pending']);

        Queue::assertPushed(ProcessCvAnalysis::class);
    }

    public function test_upload_rejects_non_pdf_file(): void
    {
        $file = UploadedFile::fake()->create('cv.docx', 500, 'application/msword');

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/job-postings/{$this->jobPosting->id}/cvs", [
                'candidate_name' => 'Test',
                'candidate_email' => 'test@example.com',
                'file' => $file,
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['file']);
    }
}