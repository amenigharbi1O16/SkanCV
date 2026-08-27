<?php
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * MISSION : table de routage API SkanCV.
 * Toutes les routes sont préfixées /api (bootstrap/app.php).
 * Routes publiques : register/login. Tout le reste : auth:api (JWT).
 */

// Register limité à 3/min par IP pour éviter la création massive de faux comptes
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:3,1');

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

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);

    Route::apiResource('job-postings', JobPostingController::class);

    // 'store' exclu ici : redéfini plus bas avec son propre throttle
    Route::apiResource('job-postings.cvs', CvController::class)
        ->except(['update', 'store']);

    Route::get('job-postings/{jobPosting}/cvs/{cv}/analysis', [AnalysisController::class, 'show'])
        ->name('job-postings.cvs.analysis.show');

    // 5 uploads par minute par utilisateur authentifié, évite le spam de la queue
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('job-postings/{jobPosting}/cvs', [CvController::class, 'store']);
        Route::post('job-postings/{jobPosting}/cvs/batch', [CvController::class, 'storeBatch']);
    });
});
