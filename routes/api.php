<?php

use App\Http\Controllers\Api\JobPostingController;
use Illuminate\Support\Facades\Route;

/**
 * Toutes les routes définies ici sont automatiquement préfixées par
 * "/api" (configuré dans bootstrap/app.php). Donc la route
 * "job-postings" ci-dessous correspond en réalité à l'URL complète
 * "/api/job-postings".
 *
 * apiResource() est un raccourci Laravel qui génère AUTOMATIQUEMENT
 * les 5 routes RESTful standard, chacune reliée à la bonne méthode
 * du Controller. C'est l'équivalent condensé d'écrire manuellement :
 *
 *   Route::get('/job-postings', [JobPostingController::class, 'index']);
 *   Route::post('/job-postings', [JobPostingController::class, 'store']);
 *   Route::get('/job-postings/{jobPosting}', [JobPostingController::class, 'show']);
 *   Route::put('/job-postings/{jobPosting}', [JobPostingController::class, 'update']);
 *   Route::delete('/job-postings/{jobPosting}', [JobPostingController::class, 'destroy']);
 */
Route::apiResource('job-postings', JobPostingController::class);