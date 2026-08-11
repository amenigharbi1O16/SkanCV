<?php

namespace App\Enums;

/**
 * Cycle de vie d'une analyse de CV.
 */
enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}