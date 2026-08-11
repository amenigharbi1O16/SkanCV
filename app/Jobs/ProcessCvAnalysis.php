<?php
// app/Jobs/ProcessCvAnalysis.php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Cv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCvAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le CV à traiter.
     * SerializesModels stocke juste l'ID en Redis (pas tout l'objet),
     * puis recharge le modèle frais depuis la DB au moment de l'exécution.
     */
    public function __construct(public Cv $cv)
    {
    }

    /**
     * Combien de fois Laravel réessaie ce job avant de l'envoyer dans failed_jobs.
     * Utile si FastAPI est temporairement indisponible (redémarrage, surcharge).
     */
    public int $tries = 3;

    /**
     * Délai en secondes avant chaque nouvelle tentative (backoff progressif).
     * Évite de re-taper sur FastAPI immédiatement s'il est déjà en difficulté.
     */
    public function backoff(): array
    {
        return [10, 30, 60]; // 10s, puis 30s, puis 60s avant d'abandonner
    }

    /**
     * Garantit qu'une seule analyse tourne à la fois, peu importe quel CV.
     * Clé globale (pas liée à un cv_id précis) car la contrainte métier est
     * "un seul traitement IA en cours dans tout le système", pas juste par CV.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('cv-analysis-global-lock'))
                ->expireAfter(120), // libère le verrou après 2min max, sécurité anti-blocage définitif
        ];
    }

    /**
     * Le cœur de la recette. Exécuté par le worker, jamais par le controller.
     */
    public function handle(): void
    {
        // On repasse le Cv en PROCESSING pour que le frontend puisse afficher
        // un indicateur "analyse en cours" si le HR Staff rafraîchit la page.
        $this->cv->analysis()->update(['status' => AnalysisStatus::PROCESSING]);

        try {
            // NOTE : l'appel réel à FastApiClient sera branché à l'étape 4.
            // Ici on prépare juste la structure d'accueil du résultat.
            //
            // $extraction = app(FastApiClient::class)->extract($this->cv->file_path);
            // $score      = app(FastApiClient::class)->score($extraction, $this->cv->jobPosting->required_skills);

            Analysis::where('cv_id', $this->cv->id)->update([
                'status' => AnalysisStatus::COMPLETED,
                // 'extracted_skills' => $extraction['skills'],
                // 'score' => $score['score'],
                // 'justification' => $score['justification'],
            ]);

        } catch (Throwable $e) {
            // Toute erreur (réseau, timeout, réponse FastAPI invalide) atterrit ici.
            Log::error('Échec analyse CV', [
                'cv_id' => $this->cv->id,
                'error' => $e->getMessage(),
            ]);

            Analysis::where('cv_id', $this->cv->id)->update([
                'status' => AnalysisStatus::FAILED,
            ]);

            // On relance l'exception pour que Laravel déclenche le retry (tries/backoff)
            throw $e;
        }
    }

    /**
     * Appelé automatiquement par Laravel si TOUTES les tentatives ont échoué
     * (donc le job atterrit dans failed_jobs).
     */
    public function failed(Throwable $exception): void
    {
        Analysis::where('cv_id', $this->cv->id)->update([
            'status' => AnalysisStatus::FAILED,
        ]);

        Log::critical('Analyse CV définitivement échouée', [
            'cv_id' => $this->cv->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}