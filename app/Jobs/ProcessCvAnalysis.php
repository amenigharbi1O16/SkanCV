<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Cv;
use App\Notifications\CvAnalysisStatusNotification;
use App\Services\FastApi\FastApiClientInterface;
use App\Support\SkillNormalizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * MISSION : orchestrer l'analyse IA d'un CV en arrière-plan.
 *
 * Déclenché par CvController après upload. Appelle FastApiClientInterface
 * (Fake ou Real selon .env), met à jour Analysis et Cv sans bloquer le HR.
 */
class ProcessCvAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Nombre de tentatives avant envoi dans failed_jobs. */
    public int $tries = 3;

    public function __construct(public Cv $cv)
    {
    }

    /** Délais progressifs entre retries (FastAPI temporairement down). */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /** Un seul traitement IA à la fois dans tout le système (protège RAM/GPU). */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('cv-analysis-global-lock'))
                ->expireAfter(120),
        ];
    }

    /**
     * Flux : PROCESSING → extract → score → COMPLETED (ou FAILED si échec définitif).
     */
    public function handle(FastApiClientInterface $fastApi): void
    {
        $this->cv->loadMissing('analysis', 'jobPosting');

        $analysis = $this->cv->analysis;

        if ($analysis === null) {
            throw new RuntimeException("Aucune Analysis trouvée pour le Cv #{$this->cv->id}");
        }

        $analysis->update(['status' => AnalysisStatus::PROCESSING]);
        $this->notifyUploader($analysis->fresh());

        try {
            if (!empty($this->cv->extracted_skills)) {
                $extraction = [
                    'text' => $this->cv->extracted_text ?? '',
                    'skills' => $this->cv->extracted_skills,
                    'candidate_name' => $this->cv->candidate_name,
                ];
            } else {
                $extraction = $fastApi->extract($this->cv->file_path);

                $cvUpdates = [
                    'extracted_text' => $extraction['text'],
                    'extracted_skills' => $extraction['skills'],
                ];

                if (!empty($extraction['candidate_name'])) {
                    $cvUpdates['candidate_name'] = $extraction['candidate_name'];
                }

                $this->cv->update($cvUpdates);
            }

            $requiredSkills = SkillNormalizer::normalize(
                $this->cv->jobPosting->required_skills ?? []
            );

            $result = $fastApi->score(
                $extraction['skills'],
                $requiredSkills
            );

            $analysis->update([
                'status' => AnalysisStatus::COMPLETED,
                'similarity_score' => $result['score'],
                'justification' => $result['justification'],
                'matching_skills' => $result['matching_skills'] ?? [],
                'missing_skills' => $result['missing_skills'] ?? [],
                'analyzed_at' => now(),
            ]);

            $this->notifyUploader($analysis->fresh());
        } catch (Throwable $e) {
            Log::error('Échec analyse CV (tentative ' . $this->attempts() . '/' . $this->tries . ')', [
                'cv_id' => $this->cv->id,
                'error' => $e->getMessage(),
            ]);

            // FAILED uniquement après épuisement des retries — voir failed()
            throw $e;
        }
    }

    /** Appelé quand le job échoue définitivement (retries épuisés). */
    public function failed(Throwable $exception): void
    {
        $this->cv->loadMissing('analysis', 'uploader');
        $this->cv->analysis()?->update(['status' => AnalysisStatus::FAILED]);

        if ($this->cv->uploader && $this->cv->analysis) {
            $this->cv->uploader->notify(
                new CvAnalysisStatusNotification($this->cv->analysis->fresh())
            );
        }

        Log::error('Analyse CV échouée', [
            'cv_id' => $this->cv->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /** Envoie une notification in-app au HR qui a uploadé le CV. */
    private function notifyUploader(Analysis $analysis): void
    {
        $this->cv->loadMissing('uploader');

        if ($this->cv->uploader) {
            $this->cv->uploader->notify(new CvAnalysisStatusNotification($analysis));
        }
    }
}
