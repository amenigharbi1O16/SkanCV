<?php

namespace Database\Factories;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        return [
            // Titre réaliste pour les tests de recherche/filtrage
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(3),
            // Liste de compétences requises, stockée en JSON dans la table
            'required_skills' => $this->faker->randomElements(
                ['PHP', 'Laravel', 'React', 'Python', 'Docker', 'MySQL'],
                3
            ),
        ];
    }
}