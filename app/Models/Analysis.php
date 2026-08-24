<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'status',
        'similarity_score',
        'justification',
        'matching_skills',
        'missing_skills',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AnalysisStatus::class,
            'similarity_score' => 'decimal:4',
            'matching_skills' => 'array',
            'missing_skills' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}