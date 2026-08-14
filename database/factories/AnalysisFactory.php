<?php

namespace Database\Factories;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Cv;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * MISSION : générer des analyses en état pending/completed pour les tests du Job.
 *
 * @extends Factory<Analysis>
 */
class AnalysisFactory extends Factory
{
    protected $model = Analysis::class;

    public function definition(): array
    {
        return [
            'cv_id' => Cv::factory(),
            'status' => AnalysisStatus::PENDING,
            'similarity_score' => null,
            'justification' => null,
            'analyzed_at' => null,
        ];
    }
}
