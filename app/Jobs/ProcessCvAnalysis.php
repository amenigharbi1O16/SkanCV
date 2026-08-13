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
use Throwable;

class ProcessCvAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Cv $cv)
    {
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('cv-analysis-global-lock'))
                ->expireAfter(120),
        ];
    }

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

    public function failed(Throwable $exception): void
    {
        $this->cv->analysis()?->update([
            'status' => AnalysisStatus::FAILED,
        ]);

        Log::critical('Analyse CV définitivement échouée', [
            'cv_id' => $this->cv->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
