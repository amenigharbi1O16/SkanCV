<?php

namespace Database\Factories;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * MISSION : générer des offres d'emploi réalistes pour les tests automatisés.
 *
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'required_skills' => ['PHP', 'Laravel', 'React'],
        ];
    }
}
