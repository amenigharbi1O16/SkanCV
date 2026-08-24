<?php

namespace App\Notifications;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification in-app (canal database) pour chaque changement d'état d'analyse CV.
 *
 * MISSION : décrire QUOI stocker en base quand le statut passe à
 * pending | processing | completed | failed. Le frontend React lit
 * ces données via GET /api/notifications.
 *
 * RELATION :
 * - Déclenchée depuis CvController (pending) et ProcessCvAnalysis (autres états)
 * - Stockée dans la table notifications via via() → ['database']
 */
class CvAnalysisStatusNotification extends Notification
{
    use Queueable;

    public function __construct(protected Analysis $analysis)
    {
    }

    /** Canal in-app uniquement — pas d'email. */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** Données JSON lues par le frontend (cloche 🔔). */
    public function toArray(object $notifiable): array
    {
        $this->analysis->loadMissing('cv.jobPosting');
        $cv = $this->analysis->cv;

        $scorePercent = $this->analysis->similarity_score !== null
            ? round((float) $this->analysis->similarity_score * 100, 1)
            : null;

        return [
            'type' => 'cv_analysis_status',
            'status' => $this->analysis->status->value,
            'analysis_id' => $this->analysis->id,
            'cv_id' => $this->analysis->cv_id,
            'job_posting_id' => $cv->job_posting_id,
            'candidate_name' => $cv->candidate_name,
            'job_posting_title' => $cv->jobPosting?->title,
            'score' => $scorePercent,
            'message' => $this->buildMessage($cv->candidate_name ?? 'Candidat', $scorePercent),
        ];
    }

    private function buildMessage(string $candidateName, ?float $scorePercent): string
    {
        return match ($this->analysis->status) {
            AnalysisStatus::PENDING => "Le CV de {$candidateName} a été reçu et est en file d'attente.",
            AnalysisStatus::PROCESSING => "L'analyse du CV de {$candidateName} est en cours...",
            AnalysisStatus::COMPLETED => $scorePercent !== null
                ? "Analyse terminée pour {$candidateName} — score : {$scorePercent}%."
                : "Analyse terminée pour {$candidateName}.",
            AnalysisStatus::FAILED => "L'analyse du CV de {$candidateName} a échoué.",
        };
    }
}
