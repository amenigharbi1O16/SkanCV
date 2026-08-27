<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Cv;
use App\Models\JobPosting;
use App\Models\User;
use App\Notifications\CvAnalysisStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = auth('api')->login($this->user);
        Storage::fake('local');
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_upload_sends_pending_notification_to_uploader(): void
    {
        Notification::fake();
        Queue::fake();

        $jobPosting = JobPosting::factory()->create();
        $file = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

        $this->withHeaders($this->authHeader())
            ->postJson("/api/job-postings/{$jobPosting->id}/cvs", [
                'candidate_name' => 'Test User',
                'candidate_email' => 'test@example.com',
                'file' => $file,
            ])
            ->assertStatus(201);

        Notification::assertSentTo(
            $this->user,
            CvAnalysisStatusNotification::class,
            fn ($notification) => $notification->toArray($this->user)['status'] === AnalysisStatus::PENDING->value
        );
    }

    public function test_notifications_index_returns_user_notifications(): void
    {
        $cv = Cv::factory()->create(['uploaded_by' => $this->user->id]);
        $analysis = Analysis::factory()->for($cv)->create([
            'status' => AnalysisStatus::COMPLETED,
            'similarity_score' => 0.85,
        ]);

        $this->user->notify(new CvAnalysisStatusNotification($analysis));

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['status' => AnalysisStatus::COMPLETED->value]);
    }

    public function test_unread_notifications_endpoint(): void
    {
        $cv = Cv::factory()->create(['uploaded_by' => $this->user->id]);
        $analysis = Analysis::factory()->for($cv)->create(['status' => AnalysisStatus::PROCESSING]);
        $this->user->notify(new CvAnalysisStatusNotification($analysis));

        $this->withHeaders($this->authHeader())
            ->getJson('/api/notifications/unread')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_mark_notification_as_read(): void
    {
        $cv = Cv::factory()->create(['uploaded_by' => $this->user->id]);
        $analysis = Analysis::factory()->for($cv)->create(['status' => AnalysisStatus::COMPLETED]);
        $this->user->notify(new CvAnalysisStatusNotification($analysis));

        $notification = $this->user->unreadNotifications->first();

        $this->withHeaders($this->authHeader())
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertStatus(200)
            ->assertJson(['status' => 'marked_as_read']);

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
