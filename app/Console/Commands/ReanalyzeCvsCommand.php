<?php

namespace App\Console\Commands;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessCvAnalysis;
use App\Models\Cv;
use App\Models\JobPosting;
use App\Support\SkillNormalizer;
use Illuminate\Console\Command;

/**
 * Corrige les required_skills mal formatées et relance l'analyse des CVs.
 */
class ReanalyzeCvsCommand extends Command
{
    protected $signature = 'cv:reanalyze {--job-posting= : ID offre uniquement} {--force : Ré-extraire même si skills déjà présentes}';

    protected $description = 'Normalise les skills des offres et relance ProcessCvAnalysis sur les CVs';

    public function handle(): int
    {
        $jobPostingId = $this->option('job-posting');

        $query = JobPosting::query();
        if ($jobPostingId) {
            $query->whereKey($jobPostingId);
        }

        $jobPostings = $query->get();

        foreach ($jobPostings as $jobPosting) {
            $normalized = SkillNormalizer::normalize($jobPosting->required_skills ?? []);
            $jobPosting->update(['required_skills' => $normalized]);
            $this->info("Offre #{$jobPosting->id} : ".count($normalized).' skills normalisées');
        }

        $cvQuery = Cv::query()->with('analysis');
        if ($jobPostingId) {
            $cvQuery->where('job_posting_id', $jobPostingId);
        }

        $count = 0;
        foreach ($cvQuery->cursor() as $cv) {
            if ($this->option('force')) {
                $cv->update(['extracted_text' => null, 'extracted_skills' => null]);
            }

            $cv->analysis()?->update([
                'status' => AnalysisStatus::PENDING,
                'similarity_score' => null,
                'justification' => null,
                'matching_skills' => null,
                'missing_skills' => null,
                'analyzed_at' => null,
            ]);

            ProcessCvAnalysis::dispatch($cv);
            $count++;
        }

        $this->info("{$count} CV(s) remis en file d'analyse.");

        return self::SUCCESS;
    }
}
