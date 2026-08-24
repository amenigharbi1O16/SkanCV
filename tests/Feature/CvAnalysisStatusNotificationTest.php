<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessCvAnalysis;
use App\Models\Analysis;
use App\Models\Cv;
use App\Models\User;
use App\Notifications\CvAnalysisStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CvAnalysisStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploader_is_notified_when_analysis_fails_definitively(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $cv = Cv::factory()->create(['uploaded_by' => $user->id]);
        $cv->analysis()->create(['status' => AnalysisStatus::PENDING]);

        $job = new ProcessCvAnalysis($cv);
        $job->failed(new \Exception('FastAPI indisponible'));

        Notification::assertSentTo(
            $user,
            CvAnalysisStatusNotification::class,
            fn ($notification) => $notification->toArray($user)['status'] === AnalysisStatus::FAILED->value
        );
    }

    public function test_no_notification_sent_when_uploader_is_unknown(): void
    {
        Notification::fake();

        $cv = Cv::factory()->create(['uploaded_by' => null]);
        $cv->analysis()->create(['status' => AnalysisStatus::PENDING]);

        $job = new ProcessCvAnalysis($cv);
        $job->failed(new \Exception('FastAPI indisponible'));

        Notification::assertNothingSent();
    }

    public function test_job_notifies_processing_and_completed_on_success(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $cv = Cv::factory()->create(['uploaded_by' => $user->id]);
        Analysis::factory()->for($cv)->create(['status' => AnalysisStatus::PENDING]);

        $job = new ProcessCvAnalysis($cv->fresh(['analysis', 'jobPosting']));
        $job->handle(new \App\Services\FastApi\FakeFastApiClient());

        Notification::assertSentTo($user, CvAnalysisStatusNotification::class, function ($notification) {
            return $notification->toArray(User::factory()->make())['status'] === AnalysisStatus::PROCESSING->value;
        });

        Notification::assertSentTo($user, CvAnalysisStatusNotification::class, function ($notification) {
            return $notification->toArray(User::factory()->make())['status'] === AnalysisStatus::COMPLETED->value;
        });
    }
}
