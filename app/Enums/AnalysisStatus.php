<?php

namespace App\Enums;

/**
 * MISSION : typage fort du cycle de vie d'une analyse CV.
 *
 * pending → processing → completed
 *                      ↘ failed (après erreur FastAPI ou retries épuisés)
 */
enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
