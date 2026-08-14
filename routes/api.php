<?php

use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * MISSION : table de routage API SkanCV.
 * Toutes les routes sont préfixées /api (bootstrap/app.php).
 * Routes publiques : register/login. Tout le reste : auth:sanctum.
 */

// Routes d'authentification publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification (Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Profil de l'utilisateur connecté
    Route::get('/me', function (Illuminate\Http\Request $request) {
        return new App\Http\Resources\UserResource($request->user());
    });

    // Gestion des offres d'emploi
    Route::apiResource('job-postings', JobPostingController::class);

    // Gestion des CVs liés à une offre d'emploi
    Route::apiResource('job-postings.cvs', CvController::class)
        ->except(['update']);

    // Consultation de l'analyse d'un CV (ressource singleton, pas d'apiResource)
    Route::get('job-postings/{jobPosting}/cvs/{cv}/analysis', [AnalysisController::class, 'show'])
        ->name('job-postings.cvs.analysis.show');
});
