<?php

namespace App\Providers;

use App\Models\JobPosting;
use App\Policies\JobPostingPolicy;
use App\Services\FastApi\FakeFastApiClient;
use App\Services\FastApi\FastApiClientInterface;
use App\Services\FastApi\RealFastApiClient;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * MISSION : configuration globale de l'application.
 *
 * Seul endroit où FakeFastApiClient et RealFastApiClient sont mentionnés
 * ensemble — le reste du code demande FastApiClientInterface au conteneur.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FastApiClientInterface::class, function () {
            return match (config('services.fastapi.mode', 'fake')) {
                'real' => new RealFastApiClient(
                    config('services.fastapi.url'),
                    config('services.fastapi.api_key'),
                ),
                default => new FakeFastApiClient(),
            };
        });
    }

    public function boot(): void
    {
        Gate::policy(JobPosting::class, JobPostingPolicy::class);
    }
}
