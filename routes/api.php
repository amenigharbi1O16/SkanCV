<?php

use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * MISSION : table de routage API SkanCV.
 * Toutes les routes sont préfixées /api (bootstrap/app.php).
 * Routes publiques : register/login. Tout le reste : auth:api (JWT).
 */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Illuminate\Http\Request $request) {
        return new App\Http\Resources\UserResource($request->user());
    });
    Route::put('/me', [ProfileController::class, 'update']);
    Route::put('/me/password', [ProfileController::class, 'updatePassword']);

    Route::get('/analyses', [AnalysisController::class, 'index'])
        ->name('analyses.index');

    Route::apiResource('job-postings', JobPostingController::class);

    Route::apiResource('job-postings.cvs', CvController::class)
        ->except(['update']);

    Route::get('job-postings/{jobPosting}/cvs/{cv}/analysis', [AnalysisController::class, 'show'])
        ->name('job-postings.cvs.analysis.show');
});