<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessCvAnalysis;
use App\Models\Analysis;
use App\Models\Cv;
use App\Models\JobPosting;
use App\Services\FastApi\FakeFastApiClient;
use App\Services\FastApi\FastApiClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Mockery;
use RuntimeException;
use Tests\TestCase;

/**
 * Tests de robustesse du pipeline d'analyse CV (Phase 1).
 */
class ProcessCvAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private function createCvWithPendingAnalysis(): Cv
    {
        $jobPosting = JobPosting::factory()->create([
            'required_skills' => ['PHP', 'Laravel', 'React'],
        ]);

        $cv = Cv::factory()->for($jobPosting)->create();

        Analysis::factory()->for($cv)->create([
            'status' => AnalysisStatus::PENDING,
        ]);

        return $cv->fresh(['analysis', 'jobPosting']);
    }

    /**
     * Scénario nominal : le Job remplit score et justification via le client IA.
     */
    public function test_job_marks_analysis_completed_on_success(): void
    {
        $cv = $this->createCvWithPendingAnalysis();

        $job = new ProcessCvAnalysis($cv);
        $job->handle(new FakeFastApiClient());

        $cv->analysis->refresh();
        $cv->refresh();

        $this->assertSame(AnalysisStatus::COMPLETED, $cv->analysis->status);
        $this->assertNotNull($cv->analysis->similarity_score);
        $this->assertNotNull($cv->analysis->justification);
        $this->assertNotNull($cv->analysis->analyzed_at);
        $this->assertNotEmpty($cv->extracted_skills);
        $this->assertNotEmpty($cv->analysis->matching_skills);
        $this->assertIsArray($cv->analysis->missing_skills);
    }

    /**
     * Chemin d'erreur transitoire : le statut reste PROCESSING pendant les retries.
     * FAILED n'est posé que dans failed() après épuisement des tentatives.
     */
    public function test_job_keeps_processing_status_when_extract_throws(): void
    {
        $cv = $this->createCvWithPendingAnalysis();

        $mock = Mockery::mock(FastApiClientInterface::class);
        $mock->shouldReceive('extract')
            ->once()
            ->andThrow(new RuntimeException('FastAPI indisponible'));

        $job = new ProcessCvAnalysis($cv);

        try {
            $job->handle($mock);
            $this->fail('Une RuntimeException était attendue.');
        } catch (RuntimeException) {
            // Comportement attendu pour déclencher le retry.
        }

        $cv->analysis->refresh();
        $this->assertSame(AnalysisStatus::PROCESSING, $cv->analysis->status);
    }

    /**
     * Après épuisement des 3 tentatives, failed() force le statut FAILED définitif.
     */
    public function test_job_failed_method_marks_analysis_failed_after_retries(): void
    {
        $cv = $this->createCvWithPendingAnalysis();

        $job = new ProcessCvAnalysis($cv);
        $job->failed(new RuntimeException('Échec définitif après 3 essais'));

        $cv->analysis->refresh();
        $this->assertSame(AnalysisStatus::FAILED, $cv->analysis->status);
    }

    /**
     * WithoutOverlapping : le Job déclare un lock global partagé entre toutes
     * les analyses (clé cv-analysis-global-lock). Test de non-régression sur
     * la configuration ; le comportement concurrent complet requiert Redis.
     */
    public function test_without_overlapping_serializes_concurrent_jobs(): void
    {
        $cv = $this->createCvWithPendingAnalysis();
        $job = new ProcessCvAnalysis($cv);

        /** @var WithoutOverlapping $middleware */
        $middleware = $job->middleware()[0];

        $this->assertInstanceOf(WithoutOverlapping::class, $middleware);

        $reflection = new \ReflectionClass($middleware);
        $keyProperty = $reflection->getProperty('key');
        $keyProperty->setAccessible(true);

        $this->assertSame('cv-analysis-global-lock', $keyProperty->getValue($middleware));
    }

    /**
     * Vérifie que le middleware WithoutOverlapping est bien configuré sur le Job.
     */
    public function test_job_uses_without_overlapping_middleware(): void
    {
        $cv = $this->createCvWithPendingAnalysis();
        $job = new ProcessCvAnalysis($cv);

        $middleware = $job->middleware();

        $this->assertNotEmpty($middleware);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[0]);
    }
}
