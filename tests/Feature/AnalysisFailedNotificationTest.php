<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessCvAnalysis;
use App\Models\Cv;
use App\Models\User;
use App\Notifications\AnalysisFailedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AnalysisFailedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Quand le job d'analyse échoue définitivement, le HR qui a uploadé
     * le CV doit recevoir la notification AnalysisFailedNotification.
     */
    public function test_uploader_is_notified_when_analysis_fails(): void
    {
        // Notification::fake() intercepte les notifications sans les envoyer
        // réellement — on vérifie juste qu'elles ont été déclenchées
        Notification::fake();

        $user = User::factory()->create();
        $cv = Cv::factory()->create(['uploaded_by' => $user->id]);

        $job = new ProcessCvAnalysis($cv);
        $job->failed(new \Exception('FastAPI indisponible'));

        Notification::assertSentTo(
            $user,
            AnalysisFailedNotification::class,
            function ($notification) use ($cv) {
                // Vérifie que la notification concerne bien le bon CV
                return $notification->cv->id === $cv->id;
            }
        );
    }

    /**
     * Si le CV n'a pas de uploader connu (uploaded_by null),
     * le job ne doit pas planter — juste ne rien notifier.
     */
    public function test_no_notification_sent_when_uploader_is_unknown(): void
    {
        Notification::fake();

        $cv = Cv::factory()->create(['uploaded_by' => null]);

        $job = new ProcessCvAnalysis($cv);
        $job->failed(new \Exception('FastAPI indisponible'));

        Notification::assertNothingSent();
    }

    /**
     * Le statut de l'analyse doit être marqué FAILED, indépendamment
     * de la notification.
     */
    public function test_analysis_status_marked_failed(): void
    {
        Notification::fake();

        $cv = Cv::factory()->create();
        $cv->analysis()->create(['status' => AnalysisStatus::PENDING]);

        $job = new ProcessCvAnalysis($cv);
        $job->failed(new \Exception('FastAPI indisponible'));

        $this->assertDatabaseHas('analyses', [
            'cv_id' => $cv->id,
            'status' => AnalysisStatus::FAILED->value,
        ]);
    }
}