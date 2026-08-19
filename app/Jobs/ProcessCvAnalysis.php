<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Models\Cv;
use App\Services\FastApi\FastApiClientInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use App\Notifications\AnalysisFailedNotification;
use Throwable;

/**
 * MISSION : orchestrer l'analyse IA d'un CV en arrière-plan.
 *
 * Déclenché par CvController@store après upload. Appelle FastApiClientInterface
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
     * Flux : PROCESSING → extract → score → COMPLETED (ou FAILED si erreur).
     * FastApiClientInterface est injecté automatiquement par Laravel.
     */
    public function handle(FastApiClientInterface $fastApi): void
    {
        $this->cv->loadMissing('analysis', 'jobPosting');

        $analysis = $this->cv->analysis;

        if ($analysis === null) {
            throw new RuntimeException("Aucune Analysis trouvée pour le Cv #{$this->cv->id}");
        }

        $analysis->update(['status' => AnalysisStatus::PROCESSING]);

        try {
            if (! empty($this->cv->extracted_skills)) {
                $extraction = [
                    'text' => $this->cv->extracted_text ?? '',
                    'skills' => $this->cv->extracted_skills,
                    'candidate_name' => $this->cv->candidate_name,
                ];
            } else {
                $extraction = $fastApi->extract($this->cv->file_path);

                $this->cv->update([
                    'extracted_text' => $extraction['text'],
                    'extracted_skills' => $extraction['skills'],
                ]);
            }

            $result = $fastApi->score(
                $extraction['skills'],
                $this->cv->jobPosting->required_skills ?? []
            );

            $analysis->update([
                'status' => AnalysisStatus::COMPLETED,
                'similarity_score' => $result['score'],
                'justification' => $result['justification'],
                'analyzed_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Échec analyse CV', [
                'cv_id' => $this->cv->id,
                'error' => $e->getMessage(),
            ]);

            $analysis->update(['status' => AnalysisStatus::FAILED]);

            throw $e;
        }
    }

    /**
 * Appelé automatiquement par Laravel quand le job échoue définitivement
 * (après épuisement des tentatives de retry configurées).
 */
    public function failed(\Throwable $exception): void
    {
        $this->cv->analysis()->update(['status' => AnalysisStatus::FAILED]);

        // Notifie le HR qui a uploadé ce CV, s'il est connu
        if ($this->cv->uploader) {
            $this->cv->uploader->notify(new AnalysisFailedNotification($this->cv));
        }

        Log::error('Analyse CV échouée', [
            'cv_id' => $this->cv->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
