<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    // setUp() s'exécute avant CHAQUE test : on authentifie systématiquement
    // un HR Staff car toutes les routes offres sont protégées par auth:api
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = auth('api')->login($this->user);
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_authenticated_user_can_create_job_posting(): void
    {
        $payload = [
            'title' => 'Développeur Laravel',
            'description' => 'Nous cherchons un développeur backend.',
            'required_skills' => ['PHP', 'Laravel', 'MySQL'],
        ];

        $response = $this->withHeaders($this->authHeader())
                          ->postJson('/api/job-postings', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Développeur Laravel']);

        $this->assertDatabaseHas('job_postings', ['title' => 'Développeur Laravel']);
    }

    public function test_creation_fails_without_required_fields(): void
    {
        $response = $this->withHeaders($this->authHeader())
                          ->postJson('/api/job-postings', ['title' => '']);

        // Vérifie que le FormRequest de validation bloque bien les champs vides
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_can_list_job_postings(): void
    {
        JobPosting::factory()->count(3)->create();

        $response = $this->withHeaders($this->authHeader())
                          ->getJson('/api/job-postings');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data'); // structure Resource paginée
    }

    public function test_can_delete_job_posting(): void
    {
        $jobPosting = JobPosting::factory()->create();

        $this->withHeaders($this->authHeader())
             ->deleteJson("/api/job-postings/{$jobPosting->id}")
             ->assertStatus(204);

        $this->assertDatabaseMissing('job_postings', ['id' => $jobPosting->id]);
    }
}