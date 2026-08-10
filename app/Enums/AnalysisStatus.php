<?php

namespace App\Enums;

/**
 * AnalysisStatus représente les états possibles du cycle de vie
 * d'une analyse de CV (table `analyses`).
 *
 * C'est un "backed enum" (enum adossé à un type scalaire ici string) :
 * chaque cas a une valeur concrète stockée en base de données.
 *
 * Cycle de vie normal :
 *   PENDING → PROCESSING → COMPLETED
 *                       └─→ FAILED (en cas d'erreur FastAPI, timeout, etc.)
 */
enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}