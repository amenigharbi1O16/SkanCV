<?php

namespace Database\Factories;

use App\Models\Cv;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class CvFactory extends Factory
{
    protected $model = Cv::class;

    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(),
            'candidate_name' => $this->faker->name(),
            'candidate_email' => $this->faker->unique()->safeEmail(),
            'file_path' => 'cvs/' . $this->faker->uuid() . '.pdf',
        ];
    }
}