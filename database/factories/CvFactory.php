<?php

namespace Database\Factories;

use App\Models\Cv;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * MISSION : générer des CVs de test sans upload PDF réel.
 *
 * @extends Factory<Cv>
 */
class CvFactory extends Factory
{
    protected $model = Cv::class;

    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(),
            'candidate_name' => fake()->name(),
            'candidate_email' => fake()->safeEmail(),
            'file_path' => 'cvs/'.fake()->uuid().'.pdf',
            'extracted_text' => null,
            'extracted_skills' => null,
        ];
    }
}
