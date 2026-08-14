<?php

namespace App\Providers;

use App\Services\FastApi\FakeFastApiClient;
use App\Services\FastApi\FastApiClientInterface;
use App\Services\FastApi\RealFastApiClient;
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
                'real' => new RealFastApiClient(config('services.fastapi.url')),
                default => new FakeFastApiClient(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
